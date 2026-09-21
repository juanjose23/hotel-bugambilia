<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Cuentas\TipoCuenta;
use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Events\Restaurante\PedidoEnviadoACocina;
use App\Interactors\Cuentas\Gestion\AbrirCuenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Interactors\Restaurante\Cocina\ConsumirIngredientesPedido;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class EnviarPedidoACocina
{
    public function __construct(
        private ConsumirIngredientesPedido $consumirIngredientes,
        private DescontarStockProductoPedido $descontarStockProducto,
        private RestauranteRepositorioInterface $repositorio,
        private AbrirCuenta $abrirCuenta,
        private RegistrarDetalleCuenta $registrarDetalle,
        private NotificadorRestaurante $notificador,
        private RecalcularTotalesPedido $recalcular,
        private BloquearItemsPorFaltaStock $bloquearItemsPorFaltaStock,
    ) {}

    /**
     * Busca un pedido por ID y lo envía a cocina.
     *
     * @throws DomainException si el pedido no existe o no está en estado ABIERTO
     */
    public function ejecutarPorId(int $pedidoId): Pedido
    {
        $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);

        if ($pedido === null) {
            throw new DomainException('Pedido no encontrado.');
        }

        return $this->ejecutar($pedido);
    }

    public function ejecutar(Pedido $pedido): Pedido
    {
        if ($pedido->estado !== EstadoPedido::ABIERTO) {
            throw new DomainException('Este pedido ya fue enviado a preparación.');
        }

        $pedido->load(['items.plato.receta', 'items.producto', 'items.variante']);

        if ($pedido->items->isEmpty()) {
            throw new DomainException('No se puede enviar un pedido a cocina sin platillos seleccionados.');
        }

        $this->bloquearItemsPorFaltaStock->ejecutar($pedido);

        return DB::transaction(function () use ($pedido): Pedido {
            $procesados = [];
            $nuevosItems = [];

            foreach ($pedido->items as $item) {
                if ($item->estado === EstadoItemPedido::PENDIENTE) {
                    $item->estado = EstadoItemPedido::EN_PREPARACION;
                    $this->repositorio->guardarItem($item);

                    if ($item->esProducto()) {
                        $this->descontarStockProducto->ejecutar($item);
                    } else {
                        $this->consumirIngredientes->ejecutar($item);
                    }

                    $procesados[] = $item->id;
                    $nuevosItems[] = $item;
                }
            }

            if ($procesados === []) {
                return $pedido;
            }

            $pedido->estado = EstadoPedido::EN_PREPARACION;

            if ($pedido->cuenta_id === null) {
                $cuenta = $this->abrirCuenta->ejecutar(
                    tipo: TipoCuenta::RESTAURANTE_DIRECTO,
                    cliente: $pedido->cliente,
                    usuarioId: $pedido->mesero_id,
                );

                $pedido->cuenta_id = $cuenta->id;
            } else {
                $cuenta = $pedido->cuenta;
            }

            // Registrar cada item procesado en la cuenta para que el saldo crezca
            if ($cuenta instanceof Cuenta) {
                foreach ($nuevosItems as $item) {
                    $nombreConcepto = match (true) {
                        $item->plato !== null => $item->plato->nombre,
                        $item->producto !== null => $item->producto->nombre.($item->variante !== null ? ' ('.$item->variante->nombre_variante.')' : ''),
                        default => 'Ítem',
                    };

                    $this->registrarDetalle->ejecutar(
                        cuenta: $cuenta,
                        concepto: $nombreConcepto.($item->observaciones ? " ({$item->observaciones})" : ''),
                        precioUnitario: (float) $item->precio_unitario,
                        cantidad: (float) $item->cantidad,
                        origen: $item,
                        espacioId: $pedido->mesa_id,
                        creadorId: $pedido->mesero_id,
                    );
                }
            }

            $this->repositorio->guardarPedido($pedido);

            event(new PedidoEnviadoACocina($pedido, $procesados));

            $this->recalcular->ejecutar($pedido);
            $this->notificador->pedidoEnviadoACocina($pedido);

            return $pedido->refresh();
        });
    }
}

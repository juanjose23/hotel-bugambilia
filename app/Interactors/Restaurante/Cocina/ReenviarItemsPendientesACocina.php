<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Events\Restaurante\PedidoEnviadoACocina;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Interactors\Restaurante\Pedidos\RecalcularTotalesPedido;
use App\Notifications\Restaurante\NotificadorRestaurante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class ReenviarItemsPendientesACocina
{
    public function __construct(
        private readonly ConsumirIngredientesPedido $consumirIngredientes,
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly RegistrarDetalleCuenta $registrarDetalle,
        private readonly NotificadorRestaurante $notificador,
        private readonly RecalcularTotalesPedido $recalcular,
    ) {}

    public function ejecutar(Pedido $pedido): Pedido
    {
        $pedido->loadMissing(['items.plato.receta', 'cuenta']);

        $itemsPendientes = $pedido->items->filter(
            fn ($item) => $item->estado === EstadoItemPedido::PENDIENTE
        );

        if ($itemsPendientes->isEmpty()) {
            throw new DomainException('No hay items pendientes para reenviar a cocina.');
        }

        return DB::transaction(function () use ($pedido, $itemsPendientes): Pedido {
            $procesados = [];

            foreach ($itemsPendientes as $item) {
                $item->estado = EstadoItemPedido::EN_PREPARACION;
                $this->repositorio->guardarItem($item);

                $this->consumirIngredientes->ejecutar($item);
                $procesados[] = $item->id;
            }

            $cuenta = $pedido->cuenta;
            if ($cuenta instanceof Cuenta && $cuenta->estaAbierta()) {
                foreach ($itemsPendientes as $item) {
                    $this->registrarDetalle->ejecutar(
                        cuenta: $cuenta,
                        concepto: ($item->plato->nombre ?? 'Platillo').($item->observaciones ? " ({$item->observaciones})" : ''),
                        precioUnitario: (float) $item->precio_unitario,
                        cantidad: (float) $item->cantidad,
                        origen: $item,
                        espacioId: $pedido->mesa_id,
                        creadorId: $pedido->mesero_id,
                    );
                }
            }

            event(new PedidoEnviadoACocina($pedido, $procesados));

            $this->recalcular->ejecutar($pedido);
            $this->notificador->pedidoEnviadoACocina($pedido);

            return $pedido->refresh();
        });
    }
}

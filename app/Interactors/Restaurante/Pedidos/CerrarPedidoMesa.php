<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CerrarPedidoMesa
{
    public function __construct(
        private readonly RegistrarDetalleCuenta $registrarDetalle,
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    /**
     * Cierra el pedido de la mesa, procesa el cobro o cargo a estancia y pasa la mesa a estado Sucio/Limpieza.
     */
    public function ejecutar(
        Pedido $pedido,
        bool $cargarAHabitacion = false,
        ?Cuenta $cuentaEstancia = null,
        ?int $usuarioId = null,
    ): Pedido {
        if ($pedido->estado === EstadoPedido::PAGADO || $pedido->estado === EstadoPedido::CARGADO_A_HABITACION) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cerrado previamente.");
        }

        return DB::transaction(function () use ($pedido, $cargarAHabitacion, $cuentaEstancia, $usuarioId): Pedido {
            $sum = $pedido->items->sum('subtotal');
            $totalPedido = is_numeric($sum) ? (float) $sum : 0.0;
            $pedido->total = $totalPedido;

            if ($cargarAHabitacion) {
                $cuenta = $cuentaEstancia ?? ($pedido->cuenta ?? null);

                if (! $cuenta instanceof Cuenta) {
                    throw new DomainException('Debe seleccionar una cuenta activa para cargar el pedido.');
                }

                $this->registrarDetalle->ejecutar(
                    cuenta: $cuenta,
                    concepto: "Consumo Restaurante (Comanda #{$pedido->codigo})",
                    precioUnitario: $totalPedido,
                    cantidad: 1,
                    origen: $pedido,
                    creadorId: $usuarioId,
                );

                $pedido->estado = EstadoPedido::CARGADO_A_HABITACION;
                $pedido->cuenta_id = $cuenta->id;
            } else {
                $pedido->estado = EstadoPedido::PAGADO;
            }

            $pedido->cerrado_en = now();
            $this->repositorio->guardarPedido($pedido);

            $mesa = $pedido->mesa;
            if ($mesa) {
                $this->repositorio->actualizarEspacio($mesa, [
                    'estado' => EstadoEspacio::Sucio,
                ]);

                $this->repositorio->crearSolicitudLimpieza([
                    'limpiable_type' => $mesa->getMorphClass(),
                    'limpiable_id' => $mesa->id,
                    'tipo' => 'limpieza',
                    'estado' => EstadoLimpieza::Pendiente,
                    'prioridad' => 'alta',
                    'notas' => "Limpieza requerida tras cierre de comanda #{$pedido->codigo}",
                    'creador_id' => $usuarioId,
                ]);
            }

            return $pedido;
        });
    }
}

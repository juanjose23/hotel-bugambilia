<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\Estancias\CategoriaConsumo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\CuentasEstancia\RegistrarConsumoCuenta;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CerrarPedidoMesa
{
    public function __construct(
        private readonly RegistrarConsumoCuenta $registrarConsumoCuenta = new RegistrarConsumoCuenta,
    ) {}

    /**
     * Cierra el pedido de la mesa, procesa el cobro o cargo a estancia y pasa la mesa a estado Sucio/Limpieza.
     */
    public function ejecutar(
        Pedido $pedido,
        bool $cargarAHabitacion = false,
        ?CuentaEstancia $cuentaEstancia = null,
        ?int $usuarioId = null,
    ): Pedido {
        if ($pedido->estado === EstadoPedido::PAGADO || $pedido->estado === EstadoPedido::CARGADO_A_HABITACION) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cerrado previamente.");
        }

        return DB::transaction(function () use ($pedido, $cargarAHabitacion, $cuentaEstancia, $usuarioId): Pedido {
            $totalPedido = (float) $pedido->items()->sum('subtotal');
            $pedido->total = $totalPedido;

            if ($cargarAHabitacion) {
                $cuenta = $cuentaEstancia ?? ($pedido->cuenta !== null ? $pedido->cuenta : null);

                if (! $cuenta instanceof CuentaEstancia) {
                    throw new DomainException('Debe seleccionar una cuenta de estancia activa para cargar el pedido.');
                }

                $this->registrarConsumoCuenta->ejecutar(
                    cuenta: $cuenta,
                    categoria: CategoriaConsumo::RESTAURANTE,
                    concepto: "Consumo Restaurante (Comanda #{$pedido->codigo})",
                    precioUnitario: $totalPedido,
                    cantidad: 1,
                    usuarioId: $usuarioId,
                    moduloOrigen: 'restaurante',
                    origenType: Pedido::class,
                    origenId: $pedido->id,
                );

                $pedido->update([
                    'cuenta_estancia_id' => $cuenta->id,
                    'estado' => EstadoPedido::CARGADO_A_HABITACION,
                    'cerrado_en' => now(),
                ]);
            } else {
                $pedido->update([
                    'estado' => EstadoPedido::PAGADO,
                    'cerrado_en' => now(),
                ]);
            }

            $mesa = $pedido->mesa;
            if ($mesa) {
                $mesa->update(['estado' => EstadoEspacio::Sucio]);

                SolicitudLimpieza::query()->create([
                    'limpiable_type' => $mesa->getMorphClass(),
                    'limpiable_id' => $mesa->id,
                    'estado' => EstadoLimpieza::Pendiente,
                    'creador_id' => $usuarioId,
                ]);
            }

            return $pedido;
        });
    }
}

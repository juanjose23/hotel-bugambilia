<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CargarPedidoACuenta
{
    public function __construct(
        private readonly RegistrarDetalleCuenta $registrarDetalle,
    ) {}

    public function ejecutar(
        Pedido $pedido,
        Cuenta $cuenta,
        ?int $usuarioId = null,
    ): Cuenta {
        if (! $cuenta->estaAbierta()) {
            throw new DomainException('La cuenta seleccionada no está abierta.');
        }

        if ($pedido->estado === EstadoPedido::CARGADO_A_HABITACION) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cargado a una cuenta.");
        }

        if ($pedido->estado === EstadoPedido::CANCELADO) {
            throw new DomainException("El pedido #{$pedido->codigo} está cancelado.");
        }

        return DB::transaction(function () use ($pedido, $cuenta, $usuarioId): Cuenta {
            $sum = $pedido->items->sum('subtotal');
            $totalPedido = is_numeric($sum) ? (float) $sum : 0.0;
            $pedido->total = $totalPedido;

            $this->registrarDetalle->ejecutar(
                cuenta: $cuenta,
                concepto: "Consumo Restaurante (Comanda #{$pedido->codigo})",
                precioUnitario: $totalPedido,
                cantidad: 1,
                origen: $pedido,
                creadorId: $usuarioId,
            );

            $pedido->update([
                'cuenta_id' => $cuenta->id,
                'estado' => EstadoPedido::CARGADO_A_HABITACION,
                'cerrado_en' => now(),
            ]);

            return $cuenta->refresh();
        });
    }
}

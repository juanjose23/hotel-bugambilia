<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante;

use App\Enums\Estancias\CategoriaConsumo;
use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\CuentasEstancia\RegistrarConsumoCuenta;
use App\Repository\Models\Estancias\CuentaEstancia;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CargarPedidoACuenta
{
    public function __construct(
        private readonly RegistrarConsumoCuenta $registrarConsumoCuenta,
    ) {}

    public function ejecutar(
        Pedido $pedido,
        CuentaEstancia $cuenta,
        ?int $usuarioId = null,
    ): CuentaEstancia {
        if (! $cuenta->estaAbierta()) {
            throw new DomainException('La cuenta seleccionada no está abierta.');
        }

        if ($pedido->estado === EstadoPedido::CARGADO_A_HABITACION) {
            throw new DomainException("El pedido #{$pedido->codigo} ya fue cargado a una cuenta.");
        }

        if ($pedido->estado === EstadoPedido::CANCELADO) {
            throw new DomainException("El pedido #{$pedido->codigo} está cancelado.");
        }

        return DB::transaction(function () use ($pedido, $cuenta, $usuarioId): CuentaEstancia {
            $totalPedido = (float) $pedido->items()->sum('subtotal');
            $pedido->total = $totalPedido;

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

            return $cuenta->refresh();
        });
    }
}

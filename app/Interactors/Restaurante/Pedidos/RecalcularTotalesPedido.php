<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Enums\Restaurante\EstadoPedido;
use App\Interactors\Cuentas\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

/**
 * Recalcula los totales de un pedido (y su cuenta asociada) después de
 * crear, actualizar o eliminar uno de sus ítems.
 */
final class RecalcularTotalesPedido
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
        private readonly RecalcularCuenta $recalcularCuenta,
    ) {}

    public function ejecutar(Pedido $pedido, bool $esItemNuevo = false): void
    {
        $this->repositorio->actualizarPedido($pedido, [
            'subtotal' => $this->repositorio->subtotalDeItemsNoAnulados($pedido),
        ]);

        if ($esItemNuevo && in_array($pedido->estado, [
            EstadoPedido::EN_PREPARACION,
            EstadoPedido::LISTO,
            EstadoPedido::SERVIDO,
        ], true)) {
            $this->repositorio->actualizarPedido($pedido, ['estado' => EstadoPedido::ABIERTO]);
        }

        $cuenta = $pedido->cuenta_id !== null
            ? $this->repositorio->obtenerCuentaPorId((int) $pedido->cuenta_id)
            : null;

        if ($cuenta instanceof Cuenta && $cuenta->estaAbierta()) {
            $this->recalcularCuenta->ejecutar($cuenta);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cuentas;

use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\AbrirCuenta;
use App\Interactors\Cuentas\TransferirPedidoACuenta;
use App\Interactors\Restaurante\Pedidos\AsignarCuentaAPedido;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Restaurante\Pedido;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class AbrirCuentaYConsumoRestaurante
{
    public function __construct(
        private AbrirCuenta $abrirCuenta,
        private TransferirPedidoACuenta $transferirPedido,
        private AsignarCuentaAPedido $asignarCuenta,
    ) {}

    /**
     * @return array{cuenta: Cuenta, detalles: array<int, CuentaDetalle>}
     */
    public function ejecutar(Pedido $pedido, ?int $usuarioId = null): array
    {
        if ($pedido->cuenta_id !== null && $pedido->cuenta !== null && $pedido->cuenta->estaAbierta()) {
            throw new DomainException('Ya existe una cuenta abierta para este pedido.');
        }

        return DB::transaction(function () use ($pedido, $usuarioId): array {
            $cuenta = $this->abrirCuenta->ejecutar(
                tipo: TipoCuenta::RESTAURANTE_DIRECTO,
                cliente: $pedido->cliente,
                usuarioId: $usuarioId,
            );

            $detalles = $this->transferirPedido->ejecutar(
                pedido: $pedido,
                cuenta: $cuenta,
                usuarioId: $usuarioId,
            );

            $this->asignarCuenta->ejecutar($pedido, (int) $cuenta->id);

            return [
                'cuenta' => $cuenta->refresh(),
                'detalles' => $detalles,
            ];
        });
    }
}

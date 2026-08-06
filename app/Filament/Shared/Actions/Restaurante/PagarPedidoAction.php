<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Shared\Actions\Cuentas\CobrarCuentaAction;
use App\Interactors\Restaurante\Cuentas\AbrirCuentaYConsumoRestaurante;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Restaurante\Pedido;
use Filament\Actions\Action;

final class PagarPedidoAction
{
    /**
     * Crea una acción unificada de "Pagar / Cerrar Comanda" utilizando la misma estructura
     * y modal de CobrarCuentaAction (resumen de cuenta, multi-moneda y vuelto).
     *
     * @param  (\Closure(mixed): ?Pedido)|null  $resolverPedido
     * @param  (\Closure(string): void)|null  $onSuccess
     */
    public static function make(?\Closure $resolverPedido = null, ?\Closure $onSuccess = null): Action
    {
        $resolver = function (mixed $record = null) use ($resolverPedido): ?Pedido {
            $pedido = null;
            if ($resolverPedido !== null) {
                $pedido = $resolverPedido($record);
            } elseif ($record instanceof Pedido) {
                $pedido = $record;
            }

            if ($pedido instanceof Pedido) {
                $pedido->loadMissing('cuenta');
            }

            return $pedido;
        };

        return CobrarCuentaAction::makeFromResolver(
            resolverCuenta: function (mixed $record = null) use ($resolver): ?Cuenta {
                $pedido = $resolver($record);

                if (! $pedido instanceof Pedido) {
                    return null;
                }

                if ($pedido->cuenta_id !== null && $pedido->cuenta !== null && $pedido->cuenta->estaAbierta()) {
                    return $pedido->cuenta;
                }

                if (in_array($pedido->estado, [EstadoPedido::PAGADO, EstadoPedido::CANCELADO], true)) {
                    return null;
                }

                $userId = auth()->id() !== null ? (int) auth()->id() : null;
                $resultado = app(AbrirCuentaYConsumoRestaurante::class)->ejecutar($pedido, $userId);

                return $resultado['cuenta'];
            },
            onSuccess: function (Cuenta $cuenta) use ($resolver, $onSuccess): void {
                if ($onSuccess !== null) {
                    $pedido = $resolver();
                    if ($pedido instanceof Pedido) {
                        $voucherUrl = route('admin.restaurante.voucher', [
                            'pedido' => $pedido->id,
                            'tipo' => 'pago',
                            'formato' => 'html',
                        ]);
                        $onSuccess($voucherUrl);
                    }
                }
            }
        )
            ->name('pagarPedido')
            ->label('Pagar / Cerrar Comanda')
            ->visible(function (mixed $record = null) use ($resolver): bool {
                $pedido = $resolver($record);

                if (! $pedido instanceof Pedido) {
                    return false;
                }

                $estado = $pedido->estado;

                if (in_array($estado, [EstadoPedido::PAGADO, EstadoPedido::CANCELADO], true)) {
                    return false;
                }

                if ($pedido->cuenta !== null && ($pedido->cuenta->estado === EstadoCuenta::CERRADA || (float) $pedido->cuenta->saldo <= 0)) {
                    return false;
                }

                return true;
            });
    }
}

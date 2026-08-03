<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Filament\Shared\Schemas\Cuentas\CamposCobroPagoForm;
use App\Filament\Shared\Schemas\Cuentas\ResumenCuentaInfolist;
use App\Filament\Shared\Schemas\Cuentas\SeccionClienteFacturacionForm;
use App\Interactors\Cuentas\ProcesarCobroCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Acción reutilizable de "Cobrar Cuenta" para el panel administrativo.
 *
 * Elimina totalmente reactividades circulares en Livewire y renderiza
 * un resumen visual limpio con procesamiento multi-moneda.
 */
final class CobrarCuentaAction
{
    /**
     * Para uso en recordActions (tabla de Cuentas). Filament inyecta el $record.
     */
    public static function make(): Action
    {
        return self::crearAccion(
            resolverCuenta: fn (mixed $record): ?Cuenta => $record instanceof Cuenta ? $record : null
        );
    }

    /**
     * Para uso en páginas Filament independientes (e.g. ViewCuenta).
     *
     * @param  \Closure(): (?Cuenta)  $resolverCuenta
     * @param  (\Closure(Cuenta): void)|null  $onSuccess
     */
    public static function makeFromResolver(\Closure $resolverCuenta, ?\Closure $onSuccess = null): Action
    {
        return self::crearAccion($resolverCuenta, $onSuccess);
    }

    /**
     * Construye la acción con su formulario modal y manejador de cobro.
     *
     * @param  \Closure(mixed): (?Cuenta)  $resolverCuenta
     * @param  (\Closure(Cuenta): void)|null  $onSuccess
     */
    private static function crearAccion(\Closure $resolverCuenta, ?\Closure $onSuccess = null): Action
    {
        return Action::make('cobrar_cuenta')
            ->label('Cobrar Cuenta')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalWidth('md')
            ->visible(function (mixed $record = null) use ($resolverCuenta): bool {
                $cuenta = $record instanceof Cuenta ? $record : $resolverCuenta($record);

                if (! $cuenta instanceof Cuenta) {
                    return false;
                }

                return in_array(
                    $cuenta->estado,
                    [EstadoCuenta::ABIERTA, EstadoCuenta::PENDIENTE_PAGO, EstadoCuenta::BLOQUEADA],
                    strict: true
                ) && (float) $cuenta->saldo > 0;
            })
            ->fillForm(function (mixed $record = null) use ($resolverCuenta): array {
                $cuenta = $record instanceof Cuenta ? $record : $resolverCuenta($record);

                if (! $cuenta instanceof Cuenta) {
                    return [];
                }

                $clienteId = $cuenta->cliente_id;
                if ($clienteId === null) {
                    $pedido = app(CuentaRepositorioInterface::class)->pedidoClienteDeCuenta($cuenta->id);

                    if ($pedido !== null) {
                        $clienteId = $pedido->cliente_id;
                    }
                }

                $monedaDefaultId = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar()?->id;

                return [
                    'cliente_id' => $clienteId,
                    'tipo_comprobante' => 'voucher',
                    'forma_pago' => MetodoPago::EFECTIVO->value,
                    'moneda_pago_id' => $monedaDefaultId,
                    'monto' => (float) $cuenta->saldo > 0 ? (float) $cuenta->saldo : 0.0,
                    'propina' => 0.0,
                    'referencia_transaccion' => null,
                    'observaciones' => null,
                ];
            })
            ->schema(function (mixed $record = null) use ($resolverCuenta): array {
                $cuenta = $record instanceof Cuenta ? $record : $resolverCuenta($record);

                return [
                    ResumenCuentaInfolist::make($cuenta),
                    CamposCobroPagoForm::make(),
                    SeccionClienteFacturacionForm::make(),
                ];
            })
            ->action(function (array $data, mixed $record = null) use ($resolverCuenta, $onSuccess): void {
                $cuenta = $record instanceof Cuenta ? $record : $resolverCuenta($record);

                if (! $cuenta instanceof Cuenta) {
                    return;
                }

                $montoCobrar = (float) ($data['monto'] ?? 0);
                $montoRecibido = (float) ($data['monto_recibido'] ?? 0);
                $monedaVueltoId = (int) ($data['moneda_vuelto_id'] ?? 0);
                $monedaVuelto = Moneda::find($monedaVueltoId);
                $codVuelto = $monedaVuelto->codigo ?? 'NIO';
                $simboloVuelto = $monedaVuelto->simbolo ?? ($codVuelto === 'USD' ? '$' : 'C$');

                $vueltoTexto = '';
                if ($montoRecibido > $montoCobrar) {
                    $diff = $montoRecibido - $montoCobrar;
                    $tasa = TasaCambio::obtenerTasa(now(), 'USD', 'NIO');
                    $monedaPagoId = (int) ($data['moneda_pago_id'] ?? 0);
                    $monedaPago = Moneda::find($monedaPagoId);
                    $codPago = $monedaPago->codigo ?? 'NIO';

                    if ($codPago === $codVuelto) {
                        $vueltoFinal = $diff;
                    } elseif ($codPago === 'USD' && $codVuelto === 'NIO') {
                        $vueltoFinal = $diff * $tasa;
                    } else {
                        $vueltoFinal = $tasa > 0 ? $diff / $tasa : $diff;
                    }

                    $vueltoTexto = "Vuelto entregado: {$simboloVuelto} ".number_format($vueltoFinal, 2)." ({$codVuelto})";
                    $obsActual = trim((string) ($data['observaciones'] ?? ''));
                    $data['observaciones'] = $obsActual !== '' ? "{$obsActual} | {$vueltoTexto}" : $vueltoTexto;
                }

                $userId = auth()->id() !== null ? (int) auth()->id() : null;

                try {
                    $resultado = app(ProcesarCobroCuenta::class)->ejecutar($cuenta, $userId, $data);

                    if ($resultado['cerrada']) {
                        $tipoLabel = ($data['tipo_comprobante'] ?? '') === 'factura_empresarial' ? 'Factura Empresarial' : 'Voucher';
                        $bodyMsg = "Cuenta #{$resultado['cuenta']->numero_cuenta} — Se generó {$tipoLabel} automáticamente.";
                        if ($vueltoTexto !== '') {
                            $bodyMsg .= " {$vueltoTexto}.";
                        }

                        Notification::make()
                            ->title('Cuenta saldada y cerrada')
                            ->body($bodyMsg)
                            ->success()
                            ->send();
                    } else {
                        $simboloCuenta = $resultado['cuenta']->moneda instanceof Moneda ? ($resultado['cuenta']->moneda->simbolo ?? 'C$') : 'C$';
                        Notification::make()
                            ->title('Abono registrado')
                            ->body("Saldo restante: {$simboloCuenta} ".number_format($resultado['saldo'], 2))
                            ->warning()
                            ->send();
                    }

                    if ($onSuccess !== null) {
                        $onSuccess($resultado['cuenta']);
                    }
                } catch (DomainException $e) {
                    Notification::make()
                        ->title('Error al procesar el pago')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}

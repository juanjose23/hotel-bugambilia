<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Cuentas;

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Filament\Shared\Schemas\Cuentas\CamposCobroPagoForm;
use App\Filament\Shared\Schemas\Cuentas\ResumenCuentaInfolist;
use App\Filament\Shared\Schemas\Cuentas\SeccionClienteFacturacionForm;
use App\Interactors\Cuentas\Cobros\CobrarCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Support\MonedaHelper;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Acción reutilizable de "Cobrar Cuenta" para el panel administrativo y móvil.
 *
 * Elimina totalmente reactividades circulares en Livewire y renderiza
 * un formulario modal optimizado por pestañas para meseros y recepción.
 */
final class CobrarCuentaAction
{
    /**
     * Para uso en recordActions (tabla de Cuentas). Filament inyecta el $record.
     */
    public static function make(): Action
    {
        return self::construir(
            resolverCuenta: fn (mixed $record): ?Cuenta => $record instanceof Cuenta ? $record : null
        );
    }

    /**
     * Para uso en tablas (recordActions en RelationManagers y Resources).
     */
    public static function makeTableAction(): Action
    {
        return self::construir(
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
        return self::construir($resolverCuenta, $onSuccess);
    }

    /**
     * Construye la acción con su formulario modal y manejador de cobro.
     *
     * @param  \Closure(mixed): (?Cuenta)  $resolverCuenta
     * @param  (\Closure(Cuenta): void)|null  $onSuccess
     */
    private static function construir(\Closure $resolverCuenta, ?\Closure $onSuccess = null): Action
    {
        return Action::make('cobrar_cuenta')
            ->label('Cobrar Cuenta')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->modalWidth('2xl')
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
                    Tabs::make('modal_cobro_tabs')
                        ->tabs([
                            Tab::make('Cobro')
                                ->icon('heroicon-o-banknotes')
                                ->schema([
                                    ResumenCuentaInfolist::makeHeader($cuenta),
                                    CamposCobroPagoForm::make(),
                                ]),

                            Tab::make('Consumos')
                                ->icon('heroicon-o-document-text')
                                ->schema([
                                    ResumenCuentaInfolist::make($cuenta),
                                ]),

                            Tab::make('Facturación')
                                ->icon('heroicon-o-user')
                                ->schema([
                                    SeccionClienteFacturacionForm::make(),
                                ]),
                        ]),
                ];
            })
            ->action(function (array $data, mixed $record = null) use ($resolverCuenta, $onSuccess): void {
                $cuenta = $record instanceof Cuenta ? $record : $resolverCuenta($record);

                if (! $cuenta instanceof Cuenta) {
                    return;
                }

                $userId = auth()->id() !== null ? (int) auth()->id() : null;

                try {
                    $resultado = app(CobrarCuenta::class)->ejecutar($cuenta, $userId, $data);

                    if ($resultado['cerrada']) {
                        $tipoLabel = ($data['tipo_comprobante'] ?? '') === 'factura_empresarial' ? 'Factura Empresarial' : 'Voucher';
                        $bodyMsg = "Cuenta #{$resultado['cuenta']->numero_cuenta} — Se generó {$tipoLabel} automáticamente.";
                        if ($resultado['vueltoTexto'] !== '') {
                            $bodyMsg .= " {$resultado['vueltoTexto']}.";
                        }
                        if ($resultado['mesaNombre'] !== null) {
                            $bodyMsg .= " La mesa {$resultado['mesaNombre']} pasó a estado Pendiente de Limpieza.";
                        }

                        Notification::make()
                            ->title('Cuenta saldada y cerrada')
                            ->body($bodyMsg)
                            ->success()
                            ->send();
                    } else {
                        $simboloCuenta = MonedaHelper::simbolo($resultado['cuenta']->moneda);
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

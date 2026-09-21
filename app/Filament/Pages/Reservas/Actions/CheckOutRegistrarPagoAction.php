<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Actions;

use App\Enums\Cuentas\MetodoPago;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class CheckOutRegistrarPagoAction
{
    public static function make(CheckOutPage $page): Action
    {
        return Action::make('registrar_pago')
            ->label('Registrar Pago de Saldo')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn (): bool => $page->reserva !== null && $page->saldoCuenta() > 0)
            ->schema([
                Select::make('forma_pago')
                    ->label('Método de Pago')
                    ->options(MetodoPago::class)
                    ->default(MetodoPago::EFECTIVO)
                    ->required(),
                TextInput::make('monto')
                    ->label('Monto a Pagar')
                    ->numeric()
                    ->prefix(fn (): string => MonedaHelper::codigo($page->cuentaActiva()?->moneda))
                    ->default(fn (): float => $page->saldoCuenta())
                    ->required()
                    ->minValue(0.01),
                TextInput::make('propina')
                    ->label('Propina voluntaria')
                    ->numeric()
                    ->default(0.0)
                    ->minValue(0),
                TextInput::make('referencia_transaccion')
                    ->label('N° Referencia / Voucher')
                    ->placeholder('Ej. BAC-AUTH-987654')
                    ->maxLength(100),
                Textarea::make('observaciones')
                    ->label('Observaciones')
                    ->maxLength(500)
                    ->rows(2),
            ])
            ->action(function (array $data, RegistrarPagoCuenta $registrarPago) use ($page): void {
                $cuenta = $page->cuentaActiva();
                if ($cuenta === null) {
                    return;
                }

                $metodo = $data['forma_pago'] instanceof MetodoPago
                    ? $data['forma_pago']
                    : (is_string($data['forma_pago']) ? MetodoPago::tryFrom($data['forma_pago']) : null) ?? MetodoPago::EFECTIVO;

                $usuarioId = auth()->id();
                $pago = $registrarPago->ejecutar(
                    cuenta: $cuenta,
                    metodoPago: $metodo,
                    monto: (float) $data['monto'],
                    propina: is_numeric($data['propina'] ?? null) ? (float) $data['propina'] : 0.0,
                    referenciaTransaccion: isset($data['referencia_transaccion']) && is_string($data['referencia_transaccion']) ? $data['referencia_transaccion'] : null,
                    observaciones: isset($data['observaciones']) && is_string($data['observaciones']) ? $data['observaciones'] : 'Pago liquidado en Check-Out',
                    usuarioId: is_int($usuarioId) ? $usuarioId : null,
                );

                $page->refrescarEstancia();

                $montoAbono = MonedaHelper::formatear((float) $pago->monto, $cuenta->moneda);

                Notification::make()
                    ->title('Pago registrado exitosamente')
                    ->body("Se aplicó un abono de {$montoAbono} a la cuenta #{$cuenta->numero_cuenta}.")
                    ->success()
                    ->send();
            });
    }
}

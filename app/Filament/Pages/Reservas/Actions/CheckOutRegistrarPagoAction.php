<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Actions;

use App\Enums\Cuentas\MetodoPago;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Support\Barcode\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CheckOutRegistrarPagoAction
{
    public static function make(CheckOutPage $page): Action
    {
        return Action::make('registrar_pago')
            ->label('Registrar abono o pago')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->authorize(fn (): bool => $page->cuentaActiva() !== null && Gate::allows('registrarPago', $page->cuentaActiva()))
            ->visible(fn (): bool => $page->reserva !== null && $page->cuentaActiva()?->estado->permitePagos() === true && $page->saldoCuenta() > 0)
            ->schema([
                Select::make('forma_pago')
                    ->label('Método de Pago')
                    ->options(MetodoPago::class)
                    ->default(MetodoPago::EFECTIVO->value)
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

                Gate::authorize('registrarPago', $cuenta);

                $valor = $data['forma_pago'] ?? null;
                $metodo = $valor instanceof MetodoPago
                    ? $valor
                    : (is_numeric($valor) ? MetodoPago::tryFrom((int) $valor) : null);
                if ($metodo === null) {
                    throw ValidationException::withMessages(['forma_pago' => 'Seleccione una forma de pago válida.']);
                }

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

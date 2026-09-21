<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Actions;

use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripe;
use App\Interactors\Facturacion\Stripe\CrearIntentoPagoStripe;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class CheckOutPagarStripeAction
{
    public static function make(CheckOutPage $page): Action
    {
        return Action::make('pagar_stripe')
            ->label('Pagar con Stripe 💳')
            ->icon('heroicon-o-credit-card')
            ->color('primary')
            ->visible(fn (): bool => $page->reserva !== null && $page->saldoCuenta() > 0)
            ->schema([
                TextInput::make('monto')
                    ->label('Monto a Cobrar con Stripe')
                    ->numeric()
                    ->prefix(fn (): string => MonedaHelper::codigo($page->cuentaActiva()?->moneda))
                    ->default(fn (): float => $page->saldoCuenta())
                    ->required()
                    ->minValue(0.01),
                TextInput::make('payment_intent_id')
                    ->label('ID de Intento / Voucher Stripe (Opcional)')
                    ->helperText('Si ya generó el cobro en terminal o checkout Stripe, ingrese el ID (pi_...) para confirmarlo y aplicarlo a la cuenta.')
                    ->placeholder('Ej. pi_3MtwBwLkdIwHu7ix28a3tKYY')
                    ->maxLength(100),
            ])
            ->action(function (
                array $data,
                CrearIntentoPagoStripe $crearStripe,
                ConfirmarPagoStripe $confirmarStripe,
            ) use ($page): void {
                $cuenta = $page->cuentaActiva();
                if ($cuenta === null) {
                    return;
                }

                $usuarioId = auth()->id();
                $piId = is_string($data['payment_intent_id'] ?? null) && trim($data['payment_intent_id']) !== ''
                    ? trim($data['payment_intent_id'])
                    : null;

                if ($piId !== null) {
                    $res = $confirmarStripe->ejecutarParaCuenta(
                        cuenta: $cuenta,
                        paymentIntentId: $piId,
                        usuarioId: is_int($usuarioId) ? $usuarioId : null,
                    );

                    $page->refrescarEstancia();

                    $montoAcreditado = MonedaHelper::formatear((float) $res['pago']->monto, $cuenta->moneda);

                    Notification::make()
                        ->title('Pago Stripe Confirmado')
                        ->body("Se acreditó {$montoAcreditado} mediante Stripe a la cuenta #{$cuenta->numero_cuenta}.")
                        ->success()
                        ->send();
                } else {
                    $monto = (float) $data['monto'];
                    $res = $crearStripe->ejecutarParaCuenta($cuenta, $monto);

                    $page->refrescarEstancia();

                    Notification::make()
                        ->title('Intento de Pago Stripe Creado')
                        ->body("Intento {$res['payment_intent_id']} generado por {$res['moneda']} {$res['monto']}. Proceda con el cobro.")
                        ->info()
                        ->send();
                }
            });
    }
}

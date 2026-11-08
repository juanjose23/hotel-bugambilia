<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Actions;

use App\Enums\Cuentas\MetodoPago;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Facturacion\Stripe\CobrarCuentaConTarjetaStripe;
use App\Interactors\Facturacion\Stripe\ConfirmarPagoStripe;
use App\Support\Barcode\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Throwable;

final class CheckOutPagarStripeAction
{
    public static function make(CheckOutPage $page): Action
    {
        return Action::make('pagar_stripe')
            ->label('Cobro con Pasarela Stripe (Terminal POS)')
            ->modalHeading('Terminal POS Stripe — Cobro con Tarjeta')
            ->modalDescription('Datáfono virtual interactivo para lectura, simulación y cobro directo con tarjeta bancaria.')
            ->modalSubmitActionLabel('Autorizar Cobro en POS')
            ->modalWidth(Width::TwoExtraLarge)
            ->icon('heroicon-o-credit-card')
            ->color('primary')
            ->visible(fn (): bool => $page->reserva !== null && $page->saldoCuenta() > 0)
            ->schema([
                ViewField::make('pos_simulator')
                    ->view('filament.resources.reservas.partials.stripe-pos-simulator')
                    ->viewData(fn (): array => [
                        'monto' => $page->saldoCuenta(),
                        'moneda' => MonedaHelper::codigo($page->cuentaActiva()?->moneda),
                        'titular' => is_string($page->reserva?->nombre_cliente) ? $page->reserva->nombre_cliente : 'Huésped',
                    ])
                    ->dehydrated(false),

                TextInput::make('monto')
                    ->label('Monto a Cobrar')
                    ->numeric()
                    ->prefix(fn (): string => MonedaHelper::codigo($page->cuentaActiva()?->moneda))
                    ->default(fn (): float => $page->saldoCuenta())
                    ->required()
                    ->minValue(0.01),

                TextInput::make('titular')
                    ->label('Nombre del Titular de la Tarjeta')
                    ->placeholder('Ej. Ana Martínez')
                    ->default(fn (): string => is_string($page->reserva?->nombre_cliente) ? $page->reserva->nombre_cliente : '')
                    ->required(fn (Get $get): bool => empty($get('payment_intent_id')))
                    ->maxLength(120),

                TextInput::make('numero_tarjeta')
                    ->label('Número de Tarjeta')
                    ->placeholder('4242 4242 4242 4242')
                    ->helperText('Acepta tarjetas Visa, MasterCard y American Express.')
                    ->required(fn (Get $get): bool => empty($get('payment_intent_id')))
                    ->maxLength(24),

                Grid::make(3)
                    ->schema([
                        Select::make('exp_mes')
                            ->label('Mes Expiración')
                            ->options([
                                '01' => '01 - Ene',
                                '02' => '02 - Feb',
                                '03' => '03 - Mar',
                                '04' => '04 - Abr',
                                '05' => '05 - May',
                                '06' => '06 - Jun',
                                '07' => '07 - Jul',
                                '08' => '08 - Ago',
                                '09' => '09 - Sep',
                                '10' => '10 - Oct',
                                '11' => '11 - Nov',
                                '12' => '12 - Dic',
                            ])
                            ->default(now()->format('m'))
                            ->required(fn (Get $get): bool => empty($get('payment_intent_id'))),

                        Select::make('exp_anio')
                            ->label('Año Expiración')
                            ->options(
                                collect(range((int) now()->format('Y'), (int) now()->format('Y') + 12))
                                    ->mapWithKeys(fn (int $y): array => [(string) $y => (string) $y])
                                    ->all()
                            )
                            ->default(now()->format('Y'))
                            ->required(fn (Get $get): bool => empty($get('payment_intent_id'))),

                        TextInput::make('cvc')
                            ->label('CVC / CVV')
                            ->placeholder('123')
                            ->maxLength(4)
                            ->required(fn (Get $get): bool => empty($get('payment_intent_id'))),
                    ]),

                Section::make('Referencia Stripe Externa (Opcional)')
                    ->description('Solo si ya procesó previamente el cobro en terminal POS externa o link de pago.')
                    ->collapsed()
                    ->schema([
                        TextInput::make('payment_intent_id')
                            ->label('ID de Intento Stripe (pi_...)')
                            ->placeholder('Ej. pi_3MtwBwLkdIwHu7ix28a3tKYY')
                            ->maxLength(100),
                    ]),
            ])
            ->action(function (
                array $data,
                CobrarCuentaConTarjetaStripe $cobrarTarjeta,
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

                $monto = (float) $data['monto'];

                try {
                    if ($piId !== null) {
                        try {
                            $res = $confirmarStripe->ejecutarParaCuenta(
                                cuenta: $cuenta,
                                paymentIntentId: $piId,
                                usuarioId: is_int($usuarioId) ? $usuarioId : null,
                            );
                            $montoAcreditado = MonedaHelper::formatear((float) $res['pago']->monto, $cuenta->moneda);
                        } catch (Throwable) {
                            $registrarPago = app(RegistrarPagoCuenta::class);
                            $pago = $registrarPago->ejecutar(
                                cuenta: $cuenta,
                                metodoPago: MetodoPago::TARJETA_CREDITO,
                                monto: $monto,
                                referenciaTransaccion: $piId,
                                observaciones: 'Cobro de pasarela Stripe en Check-Out ('.$piId.')',
                                usuarioId: is_int($usuarioId) ? $usuarioId : null,
                            );
                            $montoAcreditado = MonedaHelper::formatear((float) $pago->monto, $cuenta->moneda);
                        }

                        $detallePago = "ID de Stripe: {$piId}";
                    } else {
                        // Cobro directo con los datos de tarjeta introducidos por el recepcionista
                        $resTarjeta = $cobrarTarjeta->ejecutar(
                            cuenta: $cuenta,
                            monto: $monto,
                            datosTarjeta: [
                                'numero' => (string) ($data['numero_tarjeta'] ?? ''),
                                'exp_mes' => (string) ($data['exp_mes'] ?? ''),
                                'exp_anio' => (string) ($data['exp_anio'] ?? ''),
                                'cvc' => (string) ($data['cvc'] ?? ''),
                                'titular' => isset($data['titular']) ? (string) $data['titular'] : null,
                            ],
                            usuarioId: is_int($usuarioId) ? $usuarioId : null,
                        );

                        $montoAcreditado = MonedaHelper::formatear((float) $resTarjeta['pago']->monto, $cuenta->moneda);
                        $detallePago = "Tarjeta {$resTarjeta['marca']} terminada en •••• {$resTarjeta['ultimos4']}";
                    }

                    $page->refrescarEstancia();

                    Notification::make()
                        ->title('Pago con Stripe Aplicado')
                        ->body("Se acreditó {$montoAcreditado} mediante pasarela Stripe ({$detallePago}) a la cuenta #{$cuenta->numero_cuenta}. Saldo actualizado.")
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Error al procesar pago Stripe')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Shared\Schemas\Cuentas;

use App\Enums\Cuentas\MetodoPago;
use App\Filament\Shared\Forms\MonedaSelect;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class CamposCobroPagoForm
{
    public static function make(): Section
    {
        return Section::make('Detalles del Pago & Vuelto')
            ->icon('heroicon-o-currency-dollar')
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    Select::make('forma_pago')
                        ->label('Método de Pago')
                        ->options(fn (): array => collect(MetodoPago::cases())
                            ->filter(fn (MetodoPago $m): bool => $m !== MetodoPago::CARGO_HABITACION)
                            ->mapWithKeys(fn (MetodoPago $m): array => [$m->value => $m->getLabel()])
                            ->all())
                        ->required()
                        ->default(MetodoPago::EFECTIVO->value)
                        ->live()
                        ->native(false)
                        ->searchable(),

                    MonedaSelect::make('moneda_pago_id')
                        ->label('Moneda de Pago')
                        ->required()
                        ->live(),

                    TextInput::make('monto')
                        ->label('Monto a Cobrar')
                        ->numeric()
                        ->required()
                        ->minValue(0.01)
                        ->live(),

                    TextInput::make('monto_recibido')
                        ->label('Monto Recibido (Paga con)')
                        ->numeric()
                        ->placeholder('Ej: 1000.00')
                        ->live(),

                    MonedaSelect::make('moneda_vuelto_id')
                        ->label('Moneda del Vuelto')
                        ->required()
                        ->default(fn () => Moneda::where('codigo', 'NIO')->first()?->id)
                        ->live(),

                    TextInput::make('propina')
                        ->label('Propina Adicional')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ]),

                TextEntry::make('calculo_vuelto')
                    ->hiddenLabel()
                    ->html()
                    ->state(function (Get $get): HtmlString {
                        $montoCobrarRaw = $get('monto');
                        $montoRecibidoRaw = $get('monto_recibido');
                        $monedaPagoIdRaw = $get('moneda_pago_id');
                        $monedaVueltoIdRaw = $get('moneda_vuelto_id');

                        $montoCobrar = is_numeric($montoCobrarRaw) ? (float) $montoCobrarRaw : 0.0;
                        $montoRecibido = is_numeric($montoRecibidoRaw) ? (float) $montoRecibidoRaw : 0.0;
                        $monedaPagoId = is_numeric($monedaPagoIdRaw) ? (int) $monedaPagoIdRaw : 0;
                        $monedaVueltoId = is_numeric($monedaVueltoIdRaw) ? (int) $monedaVueltoIdRaw : 0;

                        if ($montoRecibido <= 0 || $montoRecibido <= $montoCobrar) {
                            $diferencia = max(0, $montoCobrar - $montoRecibido);
                            if ($montoRecibido > 0 && $diferencia > 0) {
                                return new HtmlString(
                                    '<div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs font-semibold text-amber-800 dark:text-amber-300 flex items-center justify-between">'.
                                    '<span>Falta por completar el cobro:</span>'.
                                    '<span class="font-black text-sm">'.number_format($diferencia, 2).'</span>'.
                                    '</div>'
                                );
                            }

                            return new HtmlString('');
                        }

                        $vueltoMonedaPago = $montoRecibido - $montoCobrar;
                        $tasa = TasaCambio::obtenerTasa(now(), 'USD', 'NIO');

                        $monedaPago = Moneda::find($monedaPagoId);
                        $monedaVuelto = Moneda::find($monedaVueltoId);

                        $codPago = $monedaPago !== null ? $monedaPago->codigo : 'NIO';
                        $codVuelto = $monedaVuelto !== null ? $monedaVuelto->codigo : 'NIO';
                        $simboloVuelto = $monedaVuelto !== null ? $monedaVuelto->simbolo : ($codVuelto === 'USD' ? '$' : 'C$');

                        // Calcular vuelto en la moneda de entrega seleccionada
                        if ($codPago === $codVuelto) {
                            $vueltoFinal = $vueltoMonedaPago;
                            $vueltoEquivalente = $codPago === 'USD'
                                ? 'C$ '.number_format($vueltoFinal * $tasa, 2).' NIO'
                                : '$ '.number_format($tasa > 0 ? $vueltoFinal / $tasa : $vueltoFinal, 2).' USD';
                        } elseif ($codPago === 'USD' && $codVuelto === 'NIO') {
                            $vueltoFinal = $vueltoMonedaPago * $tasa;
                            $vueltoEquivalente = '$ '.number_format($vueltoMonedaPago, 2).' USD';
                        } else { // NIO a USD
                            $vueltoFinal = $tasa > 0 ? $vueltoMonedaPago / $tasa : $vueltoMonedaPago;
                            $vueltoEquivalente = 'C$ '.number_format($vueltoMonedaPago, 2).' NIO';
                        }

                        return new HtmlString(
                            '<div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-600/10 border border-emerald-500/30 dark:border-emerald-500/40 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">'.
                            '<div>'.
                            '<span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-300 block">Vuelto a entregar al cliente</span>'.
                            '<span class="text-xs text-gray-500 dark:text-gray-400">Equivalente: '.$vueltoEquivalente.' (Tasa: C$ '.number_format($tasa, 2).')</span>'.
                            '</div>'.
                            '<div class="text-right">'.
                            '<span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight block">'.$simboloVuelto.' '.number_format($vueltoFinal, 2).' '.$codVuelto.'</span>'.
                            '</div>'.
                            '</div>'
                        );
                    }),

                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    TextInput::make('referencia_transaccion')
                        ->label('Referencia / Voucher')
                        ->maxLength(100)
                        ->placeholder('Nro. de transacción…'),

                    TextInput::make('observaciones')
                        ->label('Observaciones')
                        ->maxLength(255),
                ]),
            ]);
    }
}

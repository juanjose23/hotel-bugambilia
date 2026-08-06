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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

final class CamposCobroPagoForm
{
    public static function make(): Group
    {
        return Group::make([
            Grid::make(2)->schema([
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
                    ->searchable()
                    ->extraAttributes(['dusk' => 'cobro-forma-pago'])
                    ->columnSpan(1),

                MonedaSelect::make('moneda_pago_id')
                    ->label('Moneda Pago')
                    ->required()
                    ->live()
                    ->extraAttributes(['dusk' => 'cobro-moneda-pago'])
                    ->columnSpan(1),

                TextInput::make('monto')
                    ->label('Monto a Cobrar')
                    ->numeric()
                    ->prefix(function (Get $get): string {
                        $id = $get('moneda_pago_id');
                        if (! is_numeric($id)) {
                            return 'C$';
                        }
                        $moneda = Moneda::find((int) $id);

                        return $moneda !== null ? (string) $moneda->simbolo : 'C$';
                    })
                    ->required()
                    ->minValue(0.01)
                    ->readOnly()
                    ->live()
                    ->extraInputAttributes(['dusk' => 'cobro-monto'])
                    ->columnSpan(1),

                TextInput::make('monto_recibido')
                    ->label('Paga con (Recibido)')
                    ->numeric()
                    ->prefix(function (Get $get): string {
                        $id = $get('moneda_pago_id');
                        if (! is_numeric($id)) {
                            return 'C$';
                        }
                        $moneda = Moneda::find((int) $id);

                        return $moneda !== null ? (string) $moneda->simbolo : 'C$';
                    })
                    ->placeholder('Ej: 1000.00')
                    ->live()
                    ->extraInputAttributes(['dusk' => 'cobro-monto-recibido'])
                    ->columnSpan(1),
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

                    if ($montoRecibido <= 0) {
                        return new HtmlString('');
                    }

                    $tasa = TasaCambio::obtenerTasa(now(), 'USD', 'NIO');
                    if ($tasa <= 0) {
                        $tasa = 36.65;
                    }

                    $monedaPago = Moneda::find($monedaPagoId);
                    $monedaVuelto = Moneda::find($monedaVueltoId);

                    $codPago = $monedaPago !== null ? $monedaPago->codigo : 'NIO';
                    $codVuelto = $monedaVuelto !== null ? $monedaVuelto->codigo : 'NIO';

                    // Convertir monto recibido a NIO según moneda de pago
                    $montoRecibidoNIO = strtoupper($codPago) === 'USD' ? $montoRecibido * $tasa : $montoRecibido;
                    $montoCobrarNIO = $montoCobrar; // Saldo de la cuenta está en NIO

                    $diferenciaNIO = $montoRecibidoNIO - $montoCobrarNIO;

                    if ($diferenciaNIO < -0.01) {
                        $faltaNIO = abs($diferenciaNIO);
                        $faltaDisplay = strtoupper($codPago) === 'USD'
                            ? '$ '.number_format($faltaNIO / $tasa, 2).' USD'
                            : 'C$ '.number_format($faltaNIO, 2).' NIO';

                        return new HtmlString(
                            '<div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs font-semibold text-amber-800 dark:text-amber-300 flex items-center justify-between">'.
                            '<span>Falta por cobrar:</span>'.
                            '<span class="font-black text-sm">'.$faltaDisplay.'</span>'.
                            '</div>'
                        );
                    }

                    // Vuelto a entregar en la moneda seleccionada
                    if (strtoupper($codVuelto) === 'USD') {
                        $vueltoFinal = $diferenciaNIO / $tasa;
                        $simboloVuelto = '$';
                        $vueltoEquivalente = 'C$ '.number_format($diferenciaNIO, 2).' NIO';
                    } else {
                        $vueltoFinal = $diferenciaNIO;
                        $simboloVuelto = 'C$';
                        $vueltoEquivalente = '$ '.number_format($diferenciaNIO / $tasa, 2).' USD';
                    }

                    return new HtmlString(
                        '<div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 dark:border-emerald-500/40 shadow-sm flex flex-row items-center justify-between gap-2">'.
                        '<div>'.
                        '<span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 block">Vuelto a entregar</span>'.
                        '<span class="text-[10px] text-gray-500 dark:text-gray-400">Equiv: '.$vueltoEquivalente.'</span>'.
                        '</div>'.
                        '<div class="text-right">'.
                        '<span class="text-lg sm:text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono tracking-tight block">'.$simboloVuelto.' '.number_format($vueltoFinal, 2).' '.$codVuelto.'</span>'.
                        '</div>'.
                        '</div>'
                    );
                }),

            Grid::make(2)->schema([
                MonedaSelect::make('moneda_vuelto_id')
                    ->label('Moneda Vuelto')
                    ->required()
                    ->default(fn () => Moneda::where('codigo', 'NIO')->first()?->id)
                    ->live()
                    ->columnSpan(1),

                TextInput::make('propina')
                    ->label('Propina')
                    ->numeric()
                    ->prefix(function (Get $get): string {
                        $id = $get('moneda_pago_id');
                        if (! is_numeric($id)) {
                            return 'C$';
                        }
                        $moneda = Moneda::find((int) $id);

                        return $moneda !== null ? (string) $moneda->simbolo : 'C$';
                    })
                    ->default(0)
                    ->minValue(0)
                    ->columnSpan(1),

                TextInput::make('referencia_transaccion')
                    ->label('Ref / Voucher')
                    ->maxLength(100)
                    ->placeholder('Transacción…')
                    ->columnSpan(1),

                TextInput::make('observaciones')
                    ->label('Notas')
                    ->maxLength(255)
                    ->columnSpan(1),
            ]),
        ]);
    }
}

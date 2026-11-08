<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Tables;

use App\Enums\Reservas\EstadoReserva;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Repository\Models\Reservas\Reserva;
use App\Support\Barcode\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CheckOutEstanciasTable
{
    public static function configure(Table $table, CheckOutPage $page): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with([
                        'habitacion',
                        'espacio',
                        'moneda',
                        'estancia.habitacion',
                        'estancia.cuenta.moneda',
                        'estancias.habitacion',
                        'estancias.cuenta.moneda',
                    ])
                    ->whereIn('estado', [
                        EstadoReserva::CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_OUT,
                    ])
            )
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Reserva')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->icon('heroicon-o-home')
                    ->formatStateUsing(function (Reserva $record): string {
                        $hab = $record->habitacion ?? $record->estancia->habitacion ?? $record->estancias->first()?->habitacion;
                        if ($hab !== null) {
                            return "Hab. {$hab->numero} — {$hab->nombre}";
                        }

                        return $record->espacio->nombre ?? '—';
                    })
                    ->sortable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped Titular')
                    ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha_check_in')
                    ->label('Entrada')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Salida Prevista')
                    ->date('d/m/Y')
                    ->description(fn (Reserva $record): string => $record->fecha_check_out?->isToday() ? '¡Sale Hoy!' : '')
                    ->sortable(),

                TextColumn::make('estancia.cuenta.saldo')
                    ->label('Saldo Cuenta')
                    ->formatStateUsing(function (Reserva $record): string {
                        $cuenta = $record->estancia->cuenta ?? null;
                        $saldo = $cuenta !== null ? (float) $cuenta->saldo : 0.0;
                        $moneda = $cuenta->moneda ?? $record->moneda;

                        if ($saldo <= 0.0) {
                            return MonedaHelper::formatear(0.0, $moneda).' (Al día)';
                        }

                        return MonedaHelper::formatear($saldo, $moneda).' (Pendiente)';
                    })
                    ->badge()
                    ->color(function (Reserva $record): string {
                        $cuenta = $record->estancia->cuenta ?? null;
                        $saldo = $cuenta !== null ? (float) $cuenta->saldo : 0.0;

                        return $saldo <= 0.0 ? 'success' : 'danger';
                    }),
            ])
            ->defaultSort('fecha_check_out')
            ->recordActions([
                Action::make('iniciar_check_out')
                    ->label('Realizar Check-Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->url(fn (Reserva $record): string => CheckOutPage::getUrl(['record' => $record->id])),

            ]);
    }
}

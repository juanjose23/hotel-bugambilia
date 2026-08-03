<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class ResumenReserva
{
    public static function make(): Section
    {
        return Section::make('Resumen de la Reserva')
            ->columnSpanFull()
            ->icon(Heroicon::ClipboardDocumentList)
            ->columns(3)
            ->schema([
                TextEntry::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary'),

                TextEntry::make('nombre_cliente')
                    ->label('Cliente'),

                TextEntry::make('telefono_cliente')
                    ->label('Teléfono')
                    ->placeholder('—'),

                TextEntry::make('email_cliente')
                    ->label('Email')
                    ->placeholder('—'),

                TextEntry::make('tipo_reserva')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon()),

                TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state !== null ? $state->getColor() : 'gray')
                    ->icon(fn ($state) => $state !== null ? $state->getIcon() : null),

                TextEntry::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('—'),

                TextEntry::make('mesas_unidas_display')
                    ->label('Mesa(s) Reservada(s) / Unidas')
                    ->state(function (Reserva $record): string {
                        if ($record->tipo_reserva !== TipoReserva::RESTAURANTE) {
                            return $record->espacio !== null ? $record->espacio->nombre : '—';
                        }

                        $record->loadMissing('detalles.reservable');
                        $mesas = $record->detalles
                            ->map(fn ($d) => $d->reservable?->nombre)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        if ($mesas === []) {
                            return $record->espacio !== null ? $record->espacio->nombre : '—';
                        }

                        return implode(' + ', $mesas);
                    })
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextEntry::make('fecha_check_in')
                    ->label('Check-In')
                    ->date('d/m/Y'),

                TextEntry::make('fecha_check_out')
                    ->label('Check-Out')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                TextEntry::make('adultos')
                    ->label('Adultos')
                    ->numeric(),

                TextEntry::make('ninos')
                    ->label('Niños')
                    ->numeric(),

                TextEntry::make('total')
                    ->label('Total')
                    ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO'),

                TextEntry::make('tipo_pago')
                    ->label('Modalidad de pago')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state !== null ? $state->getLabel() : 'Sin pago'),

                TextEntry::make('total_pagado')
                    ->label('Total pagado')
                    ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO'),

                TextEntry::make('saldo')
                    ->label('Saldo pendiente')
                    ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO'),

                IconEntry::make('solicita_cuenta')
                    ->label('Cuenta solicitada')
                    ->boolean(),

                TextEntry::make('platos_preordenados_display')
                    ->label('Pre-orden de Degustación (Platillos Pagados)')
                    ->state(function (Reserva $record): HtmlString|string {
                        $meta = $record->meta_datos;
                        $platos = is_array($meta['platos_preordenados'] ?? null) ? $meta['platos_preordenados'] : [];

                        if ($platos === []) {
                            return 'Sin platillos preordenados';
                        }

                        $itemsHtml = '';
                        foreach ($platos as $plato) {
                            if (! is_array($plato)) {
                                continue;
                            }

                            $nombreRaw = $plato['nombre'] ?? ($plato['nombre_plato'] ?? 'Platillo');
                            $nombre = is_string($nombreRaw) ? $nombreRaw : (is_numeric($nombreRaw) ? (string) $nombreRaw : 'Platillo');
                            $cant = is_numeric($plato['cantidad'] ?? null) ? (int) $plato['cantidad'] : 1;
                            $precioRaw = $plato['precio_unitario'] ?? 0;
                            $subtotalRaw = $plato['subtotal'] ?? 0;
                            $precio = number_format(is_numeric($precioRaw) ? (float) $precioRaw : 0.0, 2);
                            $subtotal = number_format(is_numeric($subtotalRaw) ? (float) $subtotalRaw : 0.0, 2);
                            $obsVal = $plato['observaciones'] ?? null;
                            $obs = is_string($obsVal) && filled($obsVal) ? ' <span class="text-xs italic text-gray-500 dark:text-gray-400">('.e($obsVal).')</span>' : '';

                            $itemsHtml .= '<li class="py-1.5 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 text-xs">'.
                                '<span><strong class="font-bold text-emerald-600 dark:text-emerald-400">'.$cant.'x</strong> '.$nombre.$obs.'</span>'.
                                '<span class="font-semibold text-gray-700 dark:text-gray-200">C$ '.$subtotal.' <span class="text-[10px] text-gray-400">(C$ '.$precio.' c/u)</span></span>'.
                                '</li>';
                        }

                        return new HtmlString('<ul class="divide-y divide-gray-100 dark:divide-gray-800 w-full bg-emerald-500/5 p-3 rounded-xl border border-emerald-500/20">'.$itemsHtml.'</ul>');
                    })
                    ->html()
                    ->columnSpanFull(),

                TextEntry::make('notas')
                    ->label('Notas')
                    ->placeholder('Sin notas')
                    ->columnSpanFull(),
            ]);
    }
}

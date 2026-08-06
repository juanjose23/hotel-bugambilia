<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\Schemas\Reserva;

use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\Reserva;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class ResumenReserva
{
    public static function make(): Group
    {
        return Group::make()
            ->columnSpanFull()
            ->schema([
                // SECCIÓN 1: CABECERA Y DATOS DEL CLIENTE
                Section::make('Información General de la Reservación')
                    ->icon(Heroicon::InformationCircle)
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 6,
                    ])
                    ->schema([
                        TextEntry::make('codigo_reserva')
                            ->label('Código')
                            ->badge()
                            ->color('primary')
                            ->copyable(),

                        TextEntry::make('tipo_reserva')
                            ->label('Tipo de Reserva')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor() ?? 'gray')
                            ->icon(fn ($state) => $state?->getIcon() ?? 'heroicon-o-bookmark'),

                        TextEntry::make('estado')
                            ->label('Estado Actual')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor() ?? 'gray')
                            ->icon(fn ($state) => $state?->getIcon() ?? 'heroicon-o-clock'),

                        TextEntry::make('nombre_cliente')
                            ->label('Cliente / Titular'),

                        TextEntry::make('telefono_cliente')
                            ->label('Teléfono')
                            ->placeholder('—'),

                        TextEntry::make('email_cliente')
                            ->label('Email')
                            ->placeholder('—'),
                    ]),

                // SECCIÓN 2: DETALLES DEL RECURSO RESERVADO Y PROGRAMACIÓN
                Section::make('Detalles del Recurso y Programación')
                    ->icon(Heroicon::CalendarDays)
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 4,
                    ])
                    ->schema([
                        TextEntry::make('recurso_asignado')
                            ->label(fn (Reserva $record): string => match ($record->tipo_reserva) {
                                TipoReserva::HABITACION => 'Habitación Asignada',
                                TipoReserva::RESTAURANTE => 'Mesa(s) / Ambiente',
                                TipoReserva::SERVICIO => 'Servicio Contratado',
                                default => 'Espacio / Recurso',
                            })
                            ->state(function (Reserva $record): string {
                                if ($record->tipo_reserva === TipoReserva::HABITACION) {
                                    return $record->habitacion !== null
                                        ? $record->habitacion->nombre.' ('.$record->habitacion->numero.')'
                                        : '—';
                                }

                                if ($record->tipo_reserva === TipoReserva::RESTAURANTE) {
                                    $record->loadMissing('detalles.reservable');
                                    $mesas = $record->detalles
                                        ->map(fn ($d) => $d->reservable?->nombre)
                                        ->filter()
                                        ->unique()
                                        ->values()
                                        ->all();

                                    if ($mesas !== []) {
                                        return implode(' + ', $mesas);
                                    }

                                    return $record->espacio !== null ? $record->espacio->nombre : '—';
                                }

                                if ($record->tipo_reserva === TipoReserva::SERVICIO) {
                                    return $record->servicio !== null ? $record->servicio->nombre : '—';
                                }

                                return $record->espacio !== null
                                    ? $record->espacio->nombre
                                    : ($record->habitacion !== null ? $record->habitacion->nombre : '—');
                            })
                            ->badge()
                            ->color('info'),

                        TextEntry::make('fecha_check_in')
                            ->label(fn (Reserva $record): string => match ($record->tipo_reserva) {
                                TipoReserva::RESTAURANTE, TipoReserva::SERVICIO => 'Fecha de la Reserva',
                                default => 'Fecha Check-In',
                            })
                            ->date('d/m/Y'),

                        TextEntry::make('fecha_check_out')
                            ->label('Fecha Check-Out')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->visible(fn (Reserva $record): bool => $record->tipo_reserva === TipoReserva::HABITACION || $record->tipo_reserva === TipoReserva::PAQUETE),

                        TextEntry::make('duracion_noches')
                            ->label('Noches de Estancia')
                            ->state(function (Reserva $record): string {
                                if (! $record->fecha_check_in instanceof Carbon || ! $record->fecha_check_out instanceof Carbon) {
                                    return '—';
                                }

                                $noches = $record->fecha_check_in->diffInDays($record->fecha_check_out);

                                return $noches > 0 ? "{$noches} noche(s)" : '1 noche';
                            })
                            ->badge()
                            ->color('gray')
                            ->visible(fn (Reserva $record): bool => $record->tipo_reserva === TipoReserva::HABITACION || $record->tipo_reserva === TipoReserva::PAQUETE),

                        TextEntry::make('hora_reserva')
                            ->label('Hora Programada')
                            ->placeholder('—')
                            ->visible(fn (Reserva $record): bool => $record->tipo_reserva === TipoReserva::RESTAURANTE || $record->tipo_reserva === TipoReserva::SERVICIO),

                        TextEntry::make('adultos')
                            ->label('Adultos / Comensales')
                            ->numeric()
                            ->suffix(' pers.'),

                        TextEntry::make('ninos')
                            ->label('Niños')
                            ->numeric()
                            ->suffix(' pers.'),

                        TextEntry::make('promocion.nombre')
                            ->label('Promoción Aplicada')
                            ->badge()
                            ->color('success')
                            ->placeholder('Ninguna'),
                    ]),

                // SECCIÓN 3: PRE-ORDEN DE DEGUSTACIÓN Y GASTRONOMÍA (Si aplica)
                Section::make('Pre-Orden de Degustación y Menú Seleccionado')
                    ->icon(Heroicon::BuildingStorefront)
                    ->collapsible()
                    ->visible(function (Reserva $record): bool {
                        $meta = $record->meta_datos;
                        $platos = is_array($meta['platos_preordenados'] ?? null) ? $meta['platos_preordenados'] : [];

                        return $platos !== [] || $record->tipo_reserva === TipoReserva::RESTAURANTE;
                    })
                    ->schema([
                        TextEntry::make('platos_preordenados_display')
                            ->hiddenLabel()
                            ->state(function (Reserva $record): HtmlString {
                                $meta = $record->meta_datos;
                                $platos = is_array($meta['platos_preordenados'] ?? null) ? $meta['platos_preordenados'] : [];

                                if ($platos === []) {
                                    return new HtmlString('<p class="text-xs italic text-gray-500 dark:text-gray-400">No se registran platillos preordenados para esta reservación.</p>');
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
                                    $obs = is_string($obsVal) && filled($obsVal) ? ' <span class="text-xs italic text-amber-600 dark:text-amber-400">('.e($obsVal).')</span>' : '';

                                    $itemsHtml .= '<li class="py-2 flex items-center justify-between border-b border-gray-100 dark:border-gray-800 text-xs">'.
                                        '<span><strong class="font-bold text-emerald-600 dark:text-emerald-400">'.$cant.'x</strong> <span class="font-medium text-gray-900 dark:text-white">'.$nombre.'</span>'.$obs.'</span>'.
                                        '<span class="font-semibold text-gray-700 dark:text-gray-200">C$ '.$subtotal.' <span class="text-[10px] text-gray-400">(C$ '.$precio.' c/u)</span></span>'.
                                        '</li>';
                                }

                                return new HtmlString('<ul class="divide-y divide-gray-100 dark:divide-gray-800 w-full bg-emerald-500/5 p-4 rounded-xl border border-emerald-500/20">'.$itemsHtml.'</ul>');
                            })
                            ->columnSpanFull(),
                    ]),

                // SECCIÓN 4: RESUMEN FINANCIERO Y ESTADO DE PAGOS
                Section::make('Resumen Financiero y Estado de Pagos')
                    ->icon(Heroicon::Banknotes)
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 6,
                    ])
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO'),

                        TextEntry::make('descuento')
                            ->label('Descuento')
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO'),

                        TextEntry::make('total')
                            ->label('Total General')
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('tipo_pago')
                            ->label('Modalidad de Pago')
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state !== null ? $state->getLabel() : 'Sin pago'),

                        TextEntry::make('total_pagado')
                            ->label('Monto Pagado')
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('saldo')
                            ->label('Saldo Pendiente')
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO')
                            ->badge()
                            ->color(fn ($state): string => (float) $state <= 0.0 ? 'success' : 'danger'),

                        IconEntry::make('solicita_cuenta')
                            ->label('Cuenta Solicitada')
                            ->state(fn (Reserva $record): bool => (bool) ($record->solicita_cuenta || $record->cuentas()->exists() || ($record->relationLoaded('estancia') && $record->estancia?->cuenta()->exists())))
                            ->boolean(),

                        TextEntry::make('limite_cuenta_solicitado')
                            ->label('Límite de Cuenta')
                            ->state(function (Reserva $record): ?float {
                                if ($record->limite_cuenta_solicitado !== null) {
                                    return (float) $record->limite_cuenta_solicitado;
                                }

                                $cuenta = $record->cuentas()->first();
                                if ($cuenta !== null && $cuenta->limite_autorizado !== null) {
                                    return (float) $cuenta->limite_autorizado;
                                }

                                return null;
                            })
                            ->money(fn ($record): string => $record->moneda !== null ? $record->moneda->codigo : 'NIO')
                            ->placeholder('—'),
                    ]),

                // SECCIÓN 5: NOTAS Y OBSERVACIONES DE LA RESERVA
                Section::make('Notas y Solicitudes Especiales')
                    ->icon(Heroicon::ChatBubbleBottomCenterText)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('notas')
                            ->hiddenLabel()
                            ->placeholder('No hay notas registradas para esta reservación.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}

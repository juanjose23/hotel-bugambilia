<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Tables;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Resources\Reservas\ReservaResource;
use App\Filament\Resources\Reservas\Schemas\Reserva\AccionesReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\InsigniaEstadoReserva;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Reservas\Reserva;
use App\Support\MonedaHelper;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReservaTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['moneda', 'habitacion', 'espacio', 'servicio']))
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped / Titular')
                    ->description(fn (Reserva $record): ?string => array_filter([$record->telefono_cliente, $record->email_cliente])[0] ?? null)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo_reserva')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('recurso_resumen')
                    ->label('Recurso Reservado')
                    ->state(function (Reserva $record): string {
                        return match ($record->tipo_reserva) {
                            TipoReserva::HABITACION => $record->habitacion_id !== null && $record->habitacion !== null ? $record->habitacion->nombre : '—',
                            TipoReserva::RESTAURANTE => $record->espacio_id !== null && $record->espacio !== null ? $record->espacio->nombre : '—',
                            TipoReserva::SERVICIO => $record->servicio_id !== null && $record->servicio !== null ? $record->servicio->nombre : '—',
                            TipoReserva::PAQUETE => implode(' • ', array_filter([
                                $record->habitacion_id !== null && $record->habitacion !== null ? $record->habitacion->nombre : null,
                                $record->espacio_id !== null && $record->espacio !== null ? $record->espacio->nombre : null,
                                $record->servicio_id !== null && $record->servicio !== null ? $record->servicio->nombre : null,
                            ])) ?: 'Paquete Híbrido',
                        };
                    })
                    ->placeholder('—'),

                TextColumn::make('fecha_check_in')
                    ->label('Fecha / Estancia')
                    ->date('d/m/Y')
                    ->description(fn (Reserva $record): ?string => $record->fecha_check_out ? 'Hasta: '.$record->fecha_check_out->format('d/m/Y') : null)
                    ->sortable(),

                InsigniaEstadoReserva::make(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda))
                    ->description(fn (Reserva $record): string => (float) $record->saldo > 0 ? 'Saldo: '.MonedaHelper::formatear((float) $record->saldo, $record->moneda) : 'Pagado completo')
                    ->sortable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('espacio.nombre')
                    ->label('Ambiente/Mesa')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Check-Out')
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('tipo_pago')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? 'Sin pago')
                    ->color(fn ($state): string => match ($state?->value) {
                        'pago_completo' => 'success',
                        'abono_50' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('saldo')
                    ->label('Saldo Pendiente')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda))
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('detalles_count')
                    ->label('Ítems')
                    ->counts('detalles')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('detalles.reservable.nombre')
                    ->label('Recursos')
                    ->badge()
                    ->separator(',')
                    ->limitList(3)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                FiltroEstado::make(TipoReserva::class, 'tipo_reserva'),
                FiltroEstado::make(EstadoReserva::class),
            ])
            ->recordUrl(fn (Reserva $record): string => ReservaResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()->iconButton(),
                ActionGroup::make([
                    EditAction::make(),
                    ...AccionesReserva::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->color('gray')
                    ->tooltip('Acciones'),
            ]);
    }
}

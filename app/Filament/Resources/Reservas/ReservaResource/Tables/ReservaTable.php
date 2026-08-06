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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('moneda'))
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped / Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo_reserva')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('espacio.nombre')
                    ->label('Ambiente/Mesa')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('fecha_check_in')
                    ->label('Check-In')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Check-Out')
                    ->date('Y-m-d')
                    ->placeholder('-')
                    ->sortable(),

                InsigniaEstadoReserva::make(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn ($record): string => $record->moneda->codigo ?? 'NIO')
                    ->sortable(),

                TextColumn::make('tipo_pago')
                    ->label('Pago')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state?->getLabel() ?? 'Sin pago')
                    ->color(fn ($state): string => match ($state?->value) {
                        'pago_completo' => 'success',
                        'abono_50' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('total_pagado')
                    ->label('Pagado')
                    ->money(fn ($record): string => $record->moneda->codigo ?? 'NIO')
                    ->toggleable(),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money(fn ($record): string => $record->moneda->codigo ?? 'NIO')
                    ->color(fn ($state): string => (float) $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('detalles_count')
                    ->label('Ítems')
                    ->counts('detalles')
                    ->numeric(),

                TextColumn::make('detalles.reservable.nombre')
                    ->label('Recursos')
                    ->badge()
                    ->separator(',')
                    ->limitList(3),
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

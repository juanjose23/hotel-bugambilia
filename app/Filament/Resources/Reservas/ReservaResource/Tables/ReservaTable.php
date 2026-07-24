<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Tables;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\AccionesReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\InsigniaEstadoReserva;
use App\Filament\Shared\Filters\FiltroEstado;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReservaTable
{
    public function configure(Table $table): Table
    {
        return $table
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
                    ->money('NIO')
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
            ->recordActions([
                ...AccionesReserva::make(),
                EditAction::make()->iconButton(),
            ]);
    }
}

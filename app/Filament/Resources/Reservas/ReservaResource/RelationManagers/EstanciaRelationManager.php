<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use App\Support\MonedaHelper;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EstanciaRelationManager extends RelationManager
{
    protected static string $relationship = 'estancias';

    protected static ?string $title = 'Estancia y cuenta';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['reserva.moneda', 'cuenta.moneda', 'habitacion']))
            ->columns([
                TextColumn::make('check_in_at')
                    ->label('Entrada real')
                    ->dateTime('d/m/Y H:i'),
                TextColumn::make('check_out_at')
                    ->label('Salida real')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En curso'),
                TextColumn::make('habitacion.numero')
                    ->label('Habitación')
                    ->placeholder('—'),
                TextColumn::make('cantidad_llaves')
                    ->label('Llaves')
                    ->numeric(),
                TextColumn::make('cuenta.numero_cuenta')
                    ->label('Folio de cuenta')
                    ->placeholder('Sin cuenta'),
                TextColumn::make('cuenta.estado')
                    ->label('Estado cuenta')
                    ->badge(),
                TextColumn::make('cuenta.total')
                    ->label('Total')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->reserva->moneda ?? $record?->cuenta?->moneda))
                    ->placeholder('—'),
                TextColumn::make('cuenta.total_pagado')
                    ->label('Pagos')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->reserva->moneda ?? $record?->cuenta?->moneda))
                    ->placeholder('—'),
                TextColumn::make('cuenta.saldo')
                    ->label('Saldo')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->reserva->moneda ?? $record?->cuenta?->moneda))
                    ->placeholder('—'),
            ]);
    }
}

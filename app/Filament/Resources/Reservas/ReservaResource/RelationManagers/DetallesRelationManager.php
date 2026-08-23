<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use App\Enums\Reservas\EstadoReservaDetalle;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Support\MonedaHelper;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Recursos y servicios reservados';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['reserva.moneda', 'reservable', 'huespedes']))
            ->columns([
                TextColumn::make('reservable.tipo')->label('Tipo')->badge(),
                TextColumn::make('reservable.nombre')->label('Recurso')->searchable(),
                EstadoBadgeColumn::make(EstadoReservaDetalle::class),
                TextColumn::make('fecha_inicio')->label('Inicio')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('fecha_fin')->label('Fin')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
                TextColumn::make('cantidad')->label('Cantidad')->numeric(),
                TextColumn::make('huespedes.nombre')->label('Huéspedes')->listWithLineBreaks()->limitList(4),
                TextColumn::make('subtotal')->label('Subtotal')->money(fn ($record): string => MonedaHelper::codigo($record?->reserva?->moneda)),
            ])
            ->defaultSort('fecha_inicio');
    }
}

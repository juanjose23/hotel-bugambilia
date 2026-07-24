<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
            ->columns([
                TextColumn::make('reservable.tipo')->label('Tipo')->badge(),
                TextColumn::make('reservable.nombre')->label('Recurso')->searchable(),
                TextColumn::make('estado')->label('Estado')->badge(),
                TextColumn::make('fecha_inicio')->label('Inicio')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('fecha_fin')->label('Fin')->dateTime('d/m/Y H:i')->placeholder('—')->sortable(),
                TextColumn::make('cantidad')->label('Cantidad')->numeric(),
                TextColumn::make('huespedes.nombre')->label('Huéspedes')->listWithLineBreaks()->limitList(4),
                TextColumn::make('subtotal')->label('Subtotal')->money('NIO'),
            ])
            ->defaultSort('fecha_inicio');
    }
}

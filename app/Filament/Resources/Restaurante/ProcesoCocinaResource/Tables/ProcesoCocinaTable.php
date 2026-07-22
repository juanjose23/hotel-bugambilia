<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProcesoCocinaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('plato.nombre')->label('Plato')->searchable()->placeholder('—'),
                TextColumn::make('cantidad_platos')->label('Platos')->sortable(),
                TextColumn::make('productoOrigen.nombre')->label('Receta')->searchable()->placeholder('—'),
                TextColumn::make('costo_total')->label('Costo C$')->money('NIO'),
                TextColumn::make('realizadoPor.name')->label('Realizado por')->placeholder('—'),
                TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }
}

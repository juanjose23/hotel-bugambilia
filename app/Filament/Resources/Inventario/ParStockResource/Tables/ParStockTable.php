<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\ParStockResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ParStockTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('variante.codigo')
                    ->label('Variante')
                    ->placeholder('Sin variante / Base')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('stock_objetivo')
                    ->label('Objetivo')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ubicacion_id')
                    ->label('Bodega')
                    ->relationship('ubicacion', 'nombre', fn ($query) => $query->where('tipo', 'almacen')),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

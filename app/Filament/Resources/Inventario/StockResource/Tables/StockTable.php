<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\StockResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockTable
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

                TextColumn::make('lote.codigo_lote')
                    ->label('Lote')
                    ->placeholder('Sin lote')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Bodega')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
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
                SelectFilter::make('lote_id')
                    ->label('Lote')
                    ->relationship('lote', 'codigo_lote'),
            ])
            ->toolbarActions([]);
    }
}

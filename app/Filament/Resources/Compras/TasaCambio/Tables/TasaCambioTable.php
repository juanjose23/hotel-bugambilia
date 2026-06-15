<?php

namespace App\Filament\Resources\Compras\TasaCambio\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasaCambioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('monedaOrigen.codigo')
                    ->label('Moneda Origen')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('monedaDestino.codigo')
                    ->label('Moneda Destino')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('tasa')
                    ->label('Tasa de Cambio')
                    ->numeric(decimalPlaces: 4)
                    ->sortable()
                    ->searchable()
                    ->suffix(' C$'),
            ])
            ->defaultSort('fecha', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

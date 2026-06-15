<?php

namespace App\Filament\Resources\Catalogos\Moneda\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MonedaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código ISO')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('simbolo')
                    ->label('Símbolo')
                    ->alignCenter(),

                IconColumn::make('es_predeterminada')
                    ->label('Predeterminada')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('codigo')
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

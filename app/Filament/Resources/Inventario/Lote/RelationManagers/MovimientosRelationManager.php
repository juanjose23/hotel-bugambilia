<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\RelationManagers;

use App\Models\Inventario\MovimientoStock;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    protected static ?string $title = 'Historial de Movimientos';

    protected static string|\BackedEnum|null $icon = 'heroicon-m-arrow-path';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('tipoCatalogo.nombre')
                    ->label('Movimiento')
                    ->badge()
                    ->color(fn (MovimientoStock $record) => match ($record->tipo) {
                        'MOV_ENTRADA' => 'success',
                        'MOV_SALIDA' => 'danger',
                        'MOV_TRANSFERENCIA' => 'info',
                        'MOV_AJUSTE' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('ubicacionOrigen.nombre')
                    ->label('Origen')
                    ->placeholder('-'),
                TextColumn::make('ubicacionDestino.nombre')
                    ->label('Destino')
                    ->placeholder('-'),
                TextColumn::make('referencia')
                    ->label('Referencia/Motivo'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}

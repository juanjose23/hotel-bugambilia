<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Tables;

use App\Filament\Shared\Actions\Restaurante\ReporteCostosCocinaAction;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ProcesoCocinaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('productoOrigen.nombre')->label('Origen')->searchable()->placeholder('—'),
                TextColumn::make('varianteOrigen.nombre_variante')->label('Variante')->searchable()->placeholder('—'),
                TextColumn::make('cantidad_procesada')->label('Cantidad procesada')->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Resultados'),
                MontoMonedaColumn::make('costo_total')->label('Costo Total'),
                MontoMonedaColumn::make('costo_por_plato')
                    ->label('Costo por Plato')
                    ->state(fn ($record): float => (float) $record->costo_por_plato),
                TextColumn::make('plato.nombre')->label('Plato')->searchable()->placeholder('—'),
                TextColumn::make('realizadoPor.name')->label('Realizado por')->placeholder('—'),
                FechaStandardColumn::make('created_at', 'Fecha'),
            ])
            ->headerActions([
                ReporteCostosCocinaAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }
}

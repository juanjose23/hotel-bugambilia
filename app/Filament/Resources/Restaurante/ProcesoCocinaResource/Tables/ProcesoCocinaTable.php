<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Tables;

use App\Filament\Shared\Actions\Restaurante\ReporteCostosCocinaAction;
use App\Repository\Models\Restaurante\ProcesoCocina;
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
                TextColumn::make('plato.nombre')->label('Plato')->searchable()->placeholder('—'),
                TextColumn::make('cantidad_platos')->label('Platos Producidos')->sortable(),
                TextColumn::make('costo_total')->label('Costo Total C$')->money('NIO')->sortable(),
                TextColumn::make('costo_por_plato')
                    ->label('Costo por Plato C$')
                    ->money('NIO')
                    ->state(fn (ProcesoCocina $record): float => $record->costo_por_plato),
                TextColumn::make('realizadoPor.name')->label('Realizado por')->placeholder('—'),
                TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
            ])
            ->headerActions([
                ReporteCostosCocinaAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make()])->icon(Heroicon::EllipsisVertical),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActPlanMantenimiento\Tables;

use App\Repository\Models\Activos\ActPlanMantenimiento;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActPlanMantenimientoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del Plan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('frecuencia_dias')
                    ->label('Frecuencia')
                    ->suffix(' días')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('mantenimientos_count')
                    ->label('Activos')
                    ->counts('mantenimientos')
                    ->badge(),

                TextColumn::make('proveedor.persona.primer_nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('costo_estimado')
                    ->label('Costo Estimado')
                    ->money(fn (ActPlanMantenimiento $record) => $record->moneda->codigo ?? 'USD')
                    ->sortable(),
            ])
            ->filters([

                // Filtros adicionales si se requieren
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Tables;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Catalogos\CatalogoTipo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CatalogosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('catalogoTipo.nombre')
                    ->label('Tipo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codigo')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                EstadoBadgeColumn::make(EstadoGeneral::class),
                FechaStandardColumn::make()
                    ->toggleable(isToggledHiddenByDefault: true),
                FechaStandardColumn::make('updated_at', 'Actualizado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('catalogo_tipo_id')
                    ->label('Tipo')
                    ->options(fn () => CatalogoTipo::query()->orderBy('nombre')->pluck('nombre', 'id')->all()),
                FiltroEstado::make(EstadoGeneral::class),
            ])
            ->actions([
                ViewAction::make()
                    ->modalWidth(Width::FourExtraLarge),
                EditAction::make()
                    ->modalHeading('Editar catálogo')
                    ->modalWidth(Width::FourExtraLarge),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

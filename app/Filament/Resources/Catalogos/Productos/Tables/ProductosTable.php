<?php

namespace App\Filament\Resources\Catalogos\Productos\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->sortable(),
                TextColumn::make('marca.nombre')
                    ->label('Marca')
                    ->sortable(),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable(),
                TextColumn::make('unidadMedida.nombre')
                    ->label('Unidad de Medida')
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state): ?string => is_string($color = TipoProducto::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => TipoProducto::labelFor($state))
                    ->sortable(),
                EstadoBadgeColumn::make(EstadoGeneral::class)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                FiltroEliminados::make(),
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_PRODUCTO->value)
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('marca_id')
                    ->label('Marca')
                    ->relationship(
                        name: 'marca',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::MARCA->value)
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('unidad_medida_id')
                    ->label('Unidad de Medida')
                    ->relationship(
                        name: 'unidadMedida',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value)
                        )
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),

                ]),
            ])
            ->emptyStateActions([

                //
                CreateAction::make(),
            ]);
    }
}

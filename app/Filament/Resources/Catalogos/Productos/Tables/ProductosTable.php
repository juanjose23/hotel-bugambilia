<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Productos\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroCategoria;
use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSearchInSession()
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                FiltroCategoria::make(CatalogoTipo::CATEGORIA_PRODUCTO),
                FiltroCategoria::make(
                    tipo: CatalogoTipo::MARCA,
                    column: 'marca_id',
                    label: 'Marca',
                    relationship: 'marca',
                ),
                FiltroCategoria::make(
                    tipo: CatalogoTipo::UNIDAD_MEDIDA,
                    column: 'unidad_medida_id',
                    label: 'Unidad de Medida',
                    relationship: 'unidadMedida',
                ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
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

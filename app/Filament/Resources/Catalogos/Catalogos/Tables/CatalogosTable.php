<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Tables;

use App\Models\Catalogos\Catalogo;
use App\UseCases\Catalogo\Commands\ActualizarCatalogo;
use App\UseCases\CatalogoTipo\Queries\ListarCatalogoTipoOptions;
use App\Enums\EstadoCatalogo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('padre.nombre')
                    ->label('Padre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codigo')
                    ->searchable(),
                TextColumn::make('nombre')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('orden')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn($state): string => EstadoCatalogo::colorFor($state))
                    ->formatStateUsing(fn($state): string => EstadoCatalogo::labelFor($state))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('catalogo_tipo_id')
                    ->label('Tipo')
                    ->options(fn() => app(ListarCatalogoTipoOptions::class)->execute()),
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoCatalogo::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->modalHeading('Editar catálogo')
                    ->using(fn(Catalogo $record, array $data) => app(ActualizarCatalogo::class)->execute($record, $data)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
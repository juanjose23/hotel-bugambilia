<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Tables;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Catalogos\CatalogoTipo;
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
                    ->color(fn ($state): ?string => is_string($color = EstadoCatalogo::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state))
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
                    ->options(fn () => CatalogoTipo::query()->orderBy('nombre')->pluck('nombre', 'id')->all()),
                FiltroEstado::make(EstadoCatalogo::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->modalHeading('Editar catálogo')
                    ->modalWidth('4xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

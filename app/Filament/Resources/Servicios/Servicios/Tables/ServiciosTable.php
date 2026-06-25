<?php

namespace App\Filament\Resources\Servicios\Servicios\Tables;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Servicios\ServicioEstado;
use App\Filament\Resources\Shared\Filters\FiltroEliminados;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiciosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagenes.url')
                    ->label('Imagen')
                    ->circular()
                    ->placeholder('-')
                    ->limit(1),

                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon(fn ($record) => $record->icono),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): ?string => is_string($color = ServicioEstado::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => ServicioEstado::labelFor($state))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Eliminado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                FiltroEstado::make(ServicioEstado::class),

                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship(
                        name: 'categoria',
                        titleAttribute: 'nombre',
                        modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                            'catalogoTipo',
                            fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_SERVICIO->value)
                        )
                    )
                    ->searchable()
                    ->preload(),

                FiltroEliminados::make(),
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
            ]);
    }
}

<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Tables;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Catalogos\TipoUbicacion;
use App\Filament\Resources\Shared\Filters\FiltroEliminados;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Catalogos\Ubicacion;
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

class UbicacionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('padre.nombre')
                    ->label('Ubicación principal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state): ?string => is_string($color = TipoUbicacion::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => TipoUbicacion::labelFor($state))
                    ->icon(function ($state): ?string {
                        $icon = TipoUbicacion::iconFor($state);

                        return $icon instanceof \BackedEnum ? (string) $icon->value : $icon;
                    })
                    ->sortable(),
                TextColumn::make('orden')
                    ->label('Orden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state): ?string => is_string($color = EstadoCatalogo::colorFor($state)) ? $color : null)
                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state))
                    ->sortable(),
            ])
            ->filters([
                FiltroEliminados::make(),
                FiltroEstado::make(EstadoCatalogo::class),
                SelectFilter::make('tipo')
                    ->options(TipoUbicacion::class)
                    ->label('Tipo'),
                SelectFilter::make('padre_id')
                    ->label('Ubicación principal')
                    ->options(
                        fn () => Ubicacion::orderBy('nombre')->pluck('nombre', 'id')->toArray()
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()->modal(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}

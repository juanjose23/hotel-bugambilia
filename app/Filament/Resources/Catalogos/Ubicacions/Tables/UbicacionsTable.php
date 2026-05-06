<?php

namespace App\Filament\Resources\Catalogos\Ubicacions\Tables;

use App\Enums\EstadoCatalogo;
use App\Enums\TipoUbicacion;
use App\Models\Catalogos\Ubicacion;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
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
                    ->color(fn ($state): string => TipoUbicacion::colorFor($state))
                    ->formatStateUsing(fn ($state): string => TipoUbicacion::labelFor($state))
                    ->icon(fn ($state): ?BackedEnum => TipoUbicacion::iconFor($state))
                    ->sortable(),
                TextColumn::make('orden')
                    ->label('Orden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state): string => EstadoCatalogo::colorFor($state))
                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state))
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('estado')
                    ->options(EstadoCatalogo::class)
                    ->label('Estado'),
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

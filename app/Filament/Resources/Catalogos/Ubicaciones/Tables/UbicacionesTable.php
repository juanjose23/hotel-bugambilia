<?php

namespace App\Filament\Resources\Catalogos\Ubicaciones\Tables;

use App\Enums\Catalogos\TipoUbicacion;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Catalogos\Ubicacion;
use BackedEnum;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UbicacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->icon(fn ($state): BackedEnum|string|null => TipoUbicacion::iconFor($state))
                    ->sortable(),
                TextColumn::make('orden')
                    ->label('Orden')
                    ->searchable()
                    ->sortable(),
                EstadoBadgeColumn::make(EstadoGeneral::class)
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                FiltroEliminados::make(),
                FiltroEstado::make(EstadoGeneral::class),
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->modal(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
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

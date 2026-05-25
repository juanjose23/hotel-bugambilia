<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoHabitacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HabitacionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['detalle', 'categoria', 'ubicacion']))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('numero')
                    ->label('Número')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->sortable(),

                TextColumn::make('detalle.capacidad_adultos')
                    ->label('Cap. Adultos')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('detalle.capacidad_ninos')
                    ->label('Cap. Niños')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('detalle.medidas')
                    ->label('Medidas (m²)')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2) : null),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state?->color() ?? 'gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('codigo')
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoHabitacion::class),
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

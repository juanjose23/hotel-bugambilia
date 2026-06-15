<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EspacioTable
{
    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['padre', 'ubicacion'])->whereNull('padre_id'))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('padre.nombre')
                    ->label('Espacio Contenedor')
                    ->sortable()
                    ->placeholder('Principal (Sin padre)'),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación Física')
                    ->sortable()
                    ->placeholder(fn ($record) => $record->padre?->ubicacion->nombre ?? '-'),

                TextColumn::make('capacidad_personas')
                    ->label('Capacidad')
                    ->alignCenter()
                    ->sortable()
                    ->suffix(' pers.'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('orden')
            ->filters([
                SelectFilter::make('tipo')
                    ->options(TipoEspacio::options()),
                FiltroEstado::make(EstadoEspacio::class),
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

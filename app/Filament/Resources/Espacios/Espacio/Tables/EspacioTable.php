<?php

declare(strict_types=1);

namespace App\Filament\Resources\Espacios\Espacio\Tables;

use App\Enums\Espacios\EstadoEspacio;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EspacioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['tipoEspacio', 'ubicacion']))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tipoEspacio.nombre')
                    ->label('Tipo')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('capacidad')
                    ->label('Capacidad')
                    ->sortable()
                    ->alignCenter()
                    ->placeholder('—')
                    ->suffix(' pers.'),

                TextColumn::make('horario')
                    ->label('Horario')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoEspacio::class),
                SelectFilter::make('tipo_espacio_id')
                    ->label('Tipo')
                    ->relationship('tipoEspacio', 'nombre'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

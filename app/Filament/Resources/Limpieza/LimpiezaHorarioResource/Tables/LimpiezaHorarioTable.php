<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Tables;

use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LimpiezaHorarioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['detalles.limpiable', 'turno']))
            ->columns([
                TextColumn::make('detalles_names')
                    ->label('Destinos / Ubicaciones')
                    ->getStateUsing(function ($record) {
                        return $record->detalles->map(function ($d) {
                            return $d->limpiable->nombre ?? 'N/A';
                        })->join(', ');
                    })
                    ->icon(Heroicon::MapPin),

                TextColumn::make('turno.nombre')
                    ->label('Turno')
                    ->sortable()
                    ->searchable()
                    ->icon(Heroicon::Clock),

                TextColumn::make('hora_estimada')
                    ->label('Hora Estimada')
                    ->time('H:i')
                    ->sortable()
                    ->icon(Heroicon::Clock),

                TextColumn::make('duracion_estimada_minutos')
                    ->label('Min./destino')
                    ->suffix(' min')
                    ->sortable()
                    ->icon(Heroicon::Clock),

                TextColumn::make('frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'diaria' => 'success',
                        'semanal' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('dia_semana')
                    ->label('Día')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Todos')
                    ->placeholder('Todos')
                    ->sortable()
                    ->icon(Heroicon::Calendar),

                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('frecuencia')
                    ->options([
                        'diaria' => 'Diaria',
                        'semanal' => 'Semanal',
                    ]),
                SelectFilter::make('dia_semana')
                    ->options([
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo',
                    ]),
                FiltroEliminados::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}

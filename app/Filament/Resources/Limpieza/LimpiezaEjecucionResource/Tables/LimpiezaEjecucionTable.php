<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\LimpiezaEjecucion;
use App\UseCases\Shared\Queries\ObtenerNombrePersona;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LimpiezaEjecucionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['limpiable', 'turno', 'colaborador.persona.personaNatural', 'colaborador.persona.personaJuridica']))
            ->columns([
                TextColumn::make('limpiable.nombre')
                    ->label('Ubicación / Área')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('limpiable_type')
                    ->label('Tipo de Ubicación')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Habitacion::class => 'primary',
                        Espacio::class => 'warning',
                        Ubicacion::class => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Habitacion::class => 'Habitación',
                        Espacio::class => 'Espacio Común',
                        Ubicacion::class => 'Ubicación Física',
                        default => 'Otro',
                    })
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('turno.nombre')
                    ->label('Turno')
                    ->sortable(),

                TextColumn::make('colaborador')
                    ->label('Colaborador')
                    ->state(function (LimpiezaEjecucion $record): string {
                        $p = $record->colaborador?->persona;

                        return $p
                            ? ObtenerNombrePersona::desde($p)
                            : 'Sin asignar';
                    })
                    ->placeholder('Sin asignar')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('hora_inicio')
                    ->label('Inicio')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('hora_fin')
                    ->label('Fin')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoLimpieza::class),
                SelectFilter::make('ubicacion_id')
                    ->label('Ubicación')
                    ->options(function () {
                        $all = Ubicacion::all();
                        $map = $all->keyBy('id');
                        $buildPath = function (Ubicacion $u) use (&$buildPath, $map): string {
                            if ($u->padre_id && $map->has($u->padre_id)) {
                                /** @var Ubicacion $padre */
                                $padre = $map->get($u->padre_id);

                                return $buildPath($padre).' ➔ '.$u->nombre;
                            }

                            return $u->nombre;
                        };
                        $result = [];
                        foreach ($all as $u) {
                            $result[$u->id] = $buildPath($u);
                        }
                        asort($result);

                        return $result;
                    })
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }
                        $selectedId = (int) $data['value'];
                        $ubicacionIds = Ubicacion::obtenerDescendientesIds($selectedId);

                        $query->where(function (Builder $q) use ($ubicacionIds) {
                            $q->where(function ($sub) use ($ubicacionIds) {
                                $sub->where('limpiable_type', Ubicacion::class)
                                    ->whereIn('limpiable_id', $ubicacionIds);
                            })->orWhere(function ($sub) use ($ubicacionIds) {
                                $sub->where('limpiable_type', Habitacion::class)
                                    ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                                        $subQuery->select('id')
                                            ->from('habitaciones')
                                            ->whereIn('ubicacion_id', $ubicacionIds);
                                    });
                            })->orWhere(function ($sub) use ($ubicacionIds) {
                                $sub->where('limpiable_type', Espacio::class)
                                    ->whereIn('limpiable_id', function ($subQuery) use ($ubicacionIds) {
                                        $subQuery->select('id')
                                            ->from('espacios')
                                            ->whereIn('ubicacion_id', $ubicacionIds);
                                    });
                            });
                        });
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

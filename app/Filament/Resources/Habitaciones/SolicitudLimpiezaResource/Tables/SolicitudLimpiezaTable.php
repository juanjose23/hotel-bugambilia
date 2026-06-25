<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\SolicitudLimpiezaResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoLimpieza;
use App\Filament\Resources\Shared\Filters\FiltroEstado;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\SolicitudLimpieza;
use App\UseCases\Limpieza\Mutations\IniciarLimpieza;
use App\UseCases\Limpieza\Mutations\TerminarLimpieza;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SolicitudLimpiezaTable
{
    /**
     * Configura la tabla para SolicitudLimpieza.
     */
    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['limpiable', 'personal', 'creador']))
            ->columns([
                TextColumn::make('limpiable.nombre')
                    ->label('Ubicación / Área')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('limpiable_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Habitacion::class => 'primary',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Habitacion::class => 'Habitación',
                        default => 'Espacio',
                    })
                    ->sortable(),

                TextColumn::make('creador.name')
                    ->label('Solicitado Por')
                    ->placeholder('Sistema / Automático')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('personal.name')
                    ->label('Personal de Limpieza')
                    ->placeholder('Sin asignar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('prioridad')
                    ->label('Prioridad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'alta' => 'danger',
                        'normal' => 'info',
                        'baja' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado Solicitud')
                    ->badge()
                    ->sortable(),

                TextColumn::make('notas')
                    ->label('Notas')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('prioridad')
                    ->options([
                        'baja' => 'Baja',
                        'normal' => 'Normal',
                        'alta' => 'Alta',
                    ]),
                FiltroEstado::make(EstadoLimpieza::class),
                SelectFilter::make('ubicacion_id')
                    ->label('Ubicación')
                    ->options(function () {
                        $all = Ubicacion::all();
                        $map = $all->keyBy('id');
                        $buildPath = function (Ubicacion $u) use (&$buildPath, $map): string {
                            if ($u->padre_id && $map->has($u->padre_id)) {
                                return $buildPath($map->get($u->padre_id)).' ➔ '.$u->nombre;
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
                ViewAction::make(),

                Action::make('iniciarLimpieza')
                    ->label('Iniciar')
                    ->icon('heroicon-m-play')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (SolicitudLimpieza $record): bool => $record->estado === EstadoLimpieza::Pendiente)
                    ->action(function (SolicitudLimpieza $record, IniciarLimpieza $iniciarLimpieza) {
                        try {
                            $iniciarLimpieza->execute($record, auth()->id());

                            Notification::make()
                                ->title('Limpieza iniciada')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al iniciar limpieza')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('terminarLimpieza')
                    ->label('Terminar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SolicitudLimpieza $record): bool => $record->estado === EstadoLimpieza::EnProgreso)
                    ->action(function (SolicitudLimpieza $record, TerminarLimpieza $terminarLimpieza) {
                        $terminarLimpieza->execute($record);

                        Notification::make()
                            ->title('Ubicación lista y disponible')
                            ->success()
                            ->send();
                    }),

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

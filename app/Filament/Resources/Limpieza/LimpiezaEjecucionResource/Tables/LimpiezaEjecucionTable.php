<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Tables;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Filament\Shared\Columns\ColaboradorNombreColumn;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Limpieza\Ubicacion\AplicarFiltroUbicacionLimpiable;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerPathUbicacion;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
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

                ColaboradorNombreColumn::make('colaborador.persona.nombre_completo')
                    ->placeholder('Sin asignar'),

                EstadoBadgeColumn::make(EstadoLimpieza::class)
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
                FiltroEstado::make(EstadoLimpieza::class),
                SelectFilter::make('ubicacion_id')
                    ->label('Ubicación')
                    ->options(fn () => app(ObtenerPathUbicacion::class)->ejecutar())
                    ->query(function (Builder $query, array $data): void {
                        app(AplicarFiltroUbicacionLimpiable::class)->execute($query, $data['value'] ?? null);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ]);
    }
}

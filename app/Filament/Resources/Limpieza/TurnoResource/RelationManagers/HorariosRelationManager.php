<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\RelationManagers;

use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HorariosRelationManager extends RelationManager
{
    protected static string $relationship = 'horarios';

    protected static ?string $title = 'Horarios Planificados Asignados';

    protected static ?string $label = 'horario';

    protected static ?string $pluralLabel = 'horarios';

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(function (LimpiezaHorario $record): string {
                $detalle = $record->detalles->first();
                $nombre = $detalle?->limpiable?->getAttribute('nombre');

                return is_string($nombre) && $nombre !== '' ? $nombre : "Horario #{$record->id}";
            })
            ->columns([
                TextColumn::make('limpiable.nombre')
                    ->label('Ubicación / Área')
                    ->sortable()
                    ->searchable()
                    ->icon(Heroicon::MapPin),

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

                TextColumn::make('hora_estimada')
                    ->label('Hora Estimada')
                    ->time('H:i')
                    ->sortable()
                    ->icon(Heroicon::Clock),

                TextColumn::make('frecuencia')
                    ->label('Frecuencia')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'diaria' ? 'success' : 'info')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                TextColumn::make('dia_semana')
                    ->label('Día de la Semana')
                    ->placeholder('Todos los días')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable()
                    ->icon(Heroicon::Calendar),
            ])
            ->headerActions([
                AssociateAction::make()
                    ->label('Asociar Horario')
                    ->modalHeading('Asociar Horario de Limpieza al Turno')
                    ->preloadRecordSelect()
                    ->recordSelect(function (Select $select) {
                        return $select
                            ->options(function () {
                                return LimpiezaHorario::whereNull('turno_id')
                                    ->with('detalles.limpiable')
                                    ->get()
                                    ->mapWithKeys(function (LimpiezaHorario $horario) {
                                        $detalle = $horario->detalles->first();

                                        if (! $detalle) {
                                            return [$horario->id => "Sin ubicacion - Est: {$horario->hora_estimada} ({$horario->frecuencia})"];
                                        }

                                        $limpiableType = $detalle->limpiable_type;
                                        $tipo = match (true) {
                                            $limpiableType === Habitacion::class => 'Habitacion',
                                            $limpiableType === Espacio::class => 'Espacio Comun',
                                            $limpiableType === Ubicacion::class => 'Ubicacion Fisica',
                                            default => 'Otro',
                                        };
                                        $nombreVal = $detalle->limpiable ? $detalle->limpiable->getAttribute('nombre') : null;
                                        $nombre = is_scalar($nombreVal) ? (string) $nombreVal : "ID: {$detalle->limpiable_id}";

                                        return [$horario->id => "{$nombre} ({$tipo}) - Est: {$horario->hora_estimada} ({$horario->frecuencia})"];
                                    })
                                    ->toArray();
                            });
                    }),
            ])
            ->actions([
                ViewAction::make(),
                DissociateAction::make()
                    ->label('Desasociar')
                    ->modalHeading('Desasociar Horario del Turno'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ]);
    }
}

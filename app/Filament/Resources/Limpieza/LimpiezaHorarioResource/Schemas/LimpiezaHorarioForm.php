<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Schemas;

use App\Actions\Limpieza\Horarios\ValidarCargaTurnoHorarioPlanificado;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Limpieza\Turno\ObtenerOpcionesTurnosActivos;
use App\Repository\Queries\Limpieza\Turno\ObtenerTurnoPorId;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerDestinosPlanificables;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LimpiezaHorarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Planificación de Horario de Limpieza')
                    ->description('Defina la recurrencia, hora estimada y el turno a cargo.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Selección masiva de destinos')
                            ->description('Filtre por una ubicación padre, como Planta Alta, y seleccione varias habitaciones, espacios o ubicaciones en una sola acción.')
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('seleccion_masiva_tipo')
                                    ->label('Tipo de destino')
                                    ->options([
                                        Habitacion::class => 'Habitaciones',
                                        Espacio::class => 'Espacios',
                                        Ubicacion::class => 'Ubicaciones físicas',
                                    ])
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::RectangleStack)
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('seleccion_masiva_ubicacion_id', null);
                                        $set('seleccion_masiva_destinos', []);
                                    })
                                    ->dehydrated(),

                                Select::make('seleccion_masiva_ubicacion_id')
                                    ->label('Ubicación padre')
                                    ->placeholder('Todas las ubicaciones')
                                    ->options(function (Get $get): array {
                                        $tipo = $get('seleccion_masiva_tipo');

                                        return app(ObtenerDestinosPlanificables::class)->ubicacionesPadre(
                                            is_string($tipo) ? $tipo : null,
                                        );
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::MapPin)
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('seleccion_masiva_destinos', []);
                                    })
                                    ->disabled(fn (Get $get): bool => ! is_string($get('seleccion_masiva_tipo')))
                                    ->dehydrated(),

                                CheckboxList::make('seleccion_masiva_destinos')
                                    ->label('Destinos encontrados')
                                    ->options(function (Get $get): array {
                                        $tipo = $get('seleccion_masiva_tipo');

                                        if (! is_string($tipo)) {
                                            return [];
                                        }

                                        return app(ObtenerDestinosPlanificables::class)->destinos(
                                            $tipo,
                                            $get('seleccion_masiva_ubicacion_id'),
                                        );
                                    })
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->columns([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                    ->columnSpanFull()
                                    ->disabled(fn (Get $get): bool => ! is_string($get('seleccion_masiva_tipo')))
                                    ->dehydrated(),
                            ]),

                        TagsInput::make('checklist')
                            ->label('Plantilla de Tareas (Checklist)')
                            ->placeholder('Nueva tarea...')
                            ->suggestions([
                                'Tender camas y cambiar sábanas',
                                'Sacudir polvo de superficies y mobiliario',
                                'Limpiar y desinfectar el cuarto de baño',
                                'Barrer y trapear los pisos',
                                'Reponer toallas limpias',
                                'Reponer amenidades',
                                'Vaciar papeleras',
                            ])
                            ->columnSpanFull(),

                        Select::make('turno_id')
                            ->label('Turno Asignado')
                            ->placeholder('Seleccione el turno')
                            ->options(fn (): array => app(ObtenerOpcionesTurnosActivos::class)->execute(incluirHorario: true))
                            ->searchable()
                            ->live()
                            ->native(false)
                            ->prefixIcon(Heroicon::Clock)
                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                if (! is_numeric($state)) {
                                    return;
                                }

                                $turno = app(ObtenerTurnoPorId::class)->execute((int) $state);

                                if ($turno) {
                                    $set('hora_estimada', $turno->hora_inicio);
                                }
                            })
                            ->helperText(fn (Get $get): string => self::resumenCargaTurno($get)),

                        TimePicker::make('hora_estimada')
                            ->label('Hora de inicio estimada')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),

                        TextInput::make('duracion_estimada_minutos')
                            ->label('Minutos por destino')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->default(30)
                            ->required()
                            ->live(onBlur: true)
                            ->suffix('min')
                            ->helperText(fn (Get $get): string => self::resumenCargaTurno($get)),

                        Select::make('frecuencia')
                            ->label('Frecuencia')
                            ->options([
                                'diaria' => 'Diaria',
                                'semanal' => 'Semanal',
                            ])
                            ->default('diaria')
                            ->required()
                            ->live()
                            ->native(false)
                            ->prefixIcon(Heroicon::ArrowPath),

                        Select::make('dia_semana')
                            ->label('Día de la Semana')
                            ->placeholder('Seleccione el día')
                            ->options([
                                'lunes' => 'Lunes',
                                'martes' => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves' => 'Jueves',
                                'viernes' => 'Viernes',
                                'sabado' => 'Sábado',
                                'domingo' => 'Domingo',
                            ])
                            ->required(fn (Get $get) => $get('frecuencia') === 'semanal')
                            ->visible(fn (Get $get) => $get('frecuencia') === 'semanal')
                            ->native(false)
                            ->prefixIcon(Heroicon::Calendar),

                        Toggle::make('activo')
                            ->label('Horario Activo')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    private static function resumenCargaTurno(Get $get): string
    {
        $turnoId = $get('turno_id');
        $destinos = is_array($get('seleccion_masiva_destinos')) ? $get('seleccion_masiva_destinos') : [];
        $cantidadDestinos = count($destinos);
        $minutosPorDestino = is_numeric($get('duracion_estimada_minutos')) ? (int) $get('duracion_estimada_minutos') : 0;

        if (! is_numeric($turnoId) || $cantidadDestinos === 0 || $minutosPorDestino <= 0) {
            return 'Seleccione turno, destinos y minutos por destino para calcular la carga del turno.';
        }

        $turno = app(ObtenerTurnoPorId::class)->execute((int) $turnoId);

        if (! $turno) {
            return 'Seleccione un turno válido para calcular la carga.';
        }

        $minutosTurno = ValidarCargaTurnoHorarioPlanificado::minutosTurno($turno);
        $minutosPlanificados = $cantidadDestinos * $minutosPorDestino;
        $diferencia = $minutosTurno - $minutosPlanificados;

        if ($diferencia < 0) {
            return "Carga: {$cantidadDestinos} destinos x {$minutosPorDestino} min = {$minutosPlanificados} min. El turno cubre {$minutosTurno} min. Faltan ".abs($diferencia).' min.';
        }

        return "Carga: {$cantidadDestinos} destinos x {$minutosPorDestino} min = {$minutosPlanificados} min. El turno cubre {$minutosTurno} min. Quedan {$diferencia} min disponibles.";
    }
}

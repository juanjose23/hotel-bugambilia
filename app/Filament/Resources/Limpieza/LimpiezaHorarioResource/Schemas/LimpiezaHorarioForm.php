<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Schemas;

use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Limpieza\Turno;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                        Repeater::make('detalles')
                            ->relationship('detalles')
                            ->label('Destinos / Ubicaciones a Limpiar')
                            ->schema([
                                Select::make('limpiable_type')
                                    ->label('Tipo')
                                    ->options([
                                        Habitacion::class => 'Habitación',
                                        Espacio::class => 'Espacio Común',
                                        Ubicacion::class => 'Ubicación Física / Zona',
                                    ])
                                    ->required()
                                    ->live()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::RectangleStack),

                                Select::make('limpiable_id')
                                    ->label('Ubicación Específica')
                                    ->placeholder('Seleccione')
                                    ->options(function (Get $get) {
                                        $type = $get('limpiable_type');
                                        if (! is_string($type) || ! class_exists($type)) {
                                            return [];
                                        }

                                        return $type::pluck('nombre', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon(Heroicon::Home),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),

                        TagsInput::make('checklist')
                            ->label('Plantilla de Tareas (Checklist)')
                            ->placeholder('Nueva tarea...')
                            ->columnSpanFull(),

                        Select::make('turno_id')
                            ->label('Turno Asignado')
                            ->placeholder('Seleccione el turno')
                            ->options(fn () => Turno::where('estado', true)->pluck('nombre', 'id')->toArray())
                            ->searchable()
                            ->native(false)
                            ->prefixIcon(Heroicon::Clock),

                        TimePicker::make('hora_estimada')
                            ->label('Hora Estimada')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),

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
}

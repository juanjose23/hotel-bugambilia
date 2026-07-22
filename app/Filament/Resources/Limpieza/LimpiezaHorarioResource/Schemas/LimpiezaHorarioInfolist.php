<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class LimpiezaHorarioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Planificación del Horario')
                    ->icon(Heroicon::Calendar)
                    ->description('Parámetros de programación, frecuencia y estado del horario.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->schema([
                        TextEntry::make('turno.nombre')
                            ->label('Turno Asignado')
                            ->placeholder('Sin turno asignado')
                            ->icon(Heroicon::Clock)
                            ->weight(FontWeight::Bold),

                        TextEntry::make('hora_estimada')
                            ->label('Hora Estimada')
                            ->icon(Heroicon::Clock)
                            ->weight(FontWeight::Medium),

                        TextEntry::make('frecuencia')
                            ->label('Frecuencia')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'diaria' ? 'success' : 'info')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->icon(Heroicon::ArrowPath),

                        TextEntry::make('dia_semana')
                            ->label('Día de la Semana')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Todos los días')
                            ->placeholder('Todos los días')
                            ->icon(Heroicon::Calendar)
                            ->badge()
                            ->color(fn (?string $state): string => $state ? 'gray' : 'primary'),

                        TextEntry::make('activo')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (EstadoGeneral|bool|int $state): string => match (true) {
                                $state instanceof EstadoGeneral => $state->getColor(),
                                (bool) $state => 'success',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (EstadoGeneral|bool|int $state): string => match (true) {
                                $state instanceof EstadoGeneral => $state->getLabel(),
                                (bool) $state => 'Activo',
                                default => 'Inactivo',
                            }),
                    ]),

                Section::make('Destinos / Ubicaciones Asignadas')
                    ->icon(Heroicon::MapPin)
                    ->description('Lista de habitaciones y áreas comunes que deben limpiarse en este horario.')
                    ->schema([
                        RepeatableEntry::make('detalles')
                            ->hiddenLabel()
                            ->grid([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 3,
                            ])
                            ->schema([
                                TextEntry::make('limpiable_type')
                                    ->label('Tipo de Ubicación')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        Habitacion::class => 'primary',
                                        Espacio::class => 'success',
                                        Ubicacion::class => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        Habitacion::class => 'Habitación',
                                        Espacio::class => 'Espacio Común',
                                        Ubicacion::class => 'Ubicación Física',
                                        default => 'Otro',
                                    }),
                                TextEntry::make('limpiable.nombre')
                                    ->label('Ubicación Específica')
                                    ->placeholder('-')
                                    ->weight(FontWeight::Bold),
                            ])
                            ->placeholder('No se han asignado destinos a este horario planificado.'),
                    ]),

                Section::make('Checklist de Tareas')
                    ->icon(Heroicon::ListBullet)
                    ->description('Plantilla de tareas obligatorias a realizar durante el proceso de limpieza.')
                    ->schema([
                        TextEntry::make('checklist')
                            ->hiddenLabel()
                            ->badge()
                            ->color('gray')
                            ->placeholder('Sin tareas registradas en esta plantilla.')
                            ->icon(Heroicon::CheckCircle),
                    ]),

                Section::make('Metadatos')
                    ->icon(Heroicon::InformationCircle)
                    ->description('Fechas de creación y actualizaciones del registro.')
                    ->collapsible()
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        ...TimestampsInfolistEntry::make(),
                    ]),
            ]);
    }
}

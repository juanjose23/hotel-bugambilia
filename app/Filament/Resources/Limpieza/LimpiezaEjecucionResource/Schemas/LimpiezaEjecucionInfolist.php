<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Schemas;

use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class LimpiezaEjecucionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Progreso y Checklist de Tareas')
                    ->icon(Heroicon::ListBullet)
                    ->description('Tareas requeridas y su estado de cumplimiento.')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('progreso_checklist')
                            ->label('Progreso general')
                            ->html()
                            ->state(function (LimpiezaEjecucion $record) {
                                $checklist = $record->detalles_checklist;
                                if (empty($checklist)) {
                                    return '<span class="text-xs text-gray-400 dark:text-gray-500">0%</span>';
                                }
                                $total = count($checklist);
                                $completed = count(array_filter($checklist));
                                $percentage = (int) (($completed / $total) * 100);

                                return new HtmlString(view('components.limpieza.checklist-progress', [
                                    'percentage' => $percentage,
                                    'completed' => $completed,
                                    'total' => $total,
                                ])->render());
                            }),

                        TextEntry::make('detalles_checklist')
                            ->label('Tareas del Checklist')
                            ->html()
                            ->state(function (LimpiezaEjecucion $record) {
                                $checklist = $record->detalles_checklist;
                                if (empty($checklist)) {
                                    return '<span class="text-sm text-gray-400 dark:text-gray-500">No hay tareas registradas o asignadas.</span>';
                                }

                                return new HtmlString(view('components.limpieza.checklist-tasks', [
                                    'checklist' => $checklist,
                                ])->render());
                            }),
                    ]),

                Section::make('Detalles de la Ejecución')
                    ->icon(Heroicon::CheckBadge)
                    ->description('Estado actual y tiempos de realización de la limpieza.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->schema([
                        TextEntry::make('limpiable.nombre')
                            ->label('Ubicación / Área')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('limpiable_type')
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
                            }),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge(),

                        TextEntry::make('fecha')
                            ->label('Fecha de Ejecución')
                            ->date('d/m/Y')
                            ->icon(Heroicon::Calendar),

                        TextEntry::make('hora_inicio')
                            ->label('Hora de Inicio')
                            ->placeholder('—')
                            ->icon(Heroicon::Clock),

                        TextEntry::make('hora_fin')
                            ->label('Hora de Fin')
                            ->placeholder('—')
                            ->icon(Heroicon::Clock),

                        TextEntry::make('duracion')
                            ->label('Duración')
                            ->icon(Heroicon::Clock)
                            ->state(function (LimpiezaEjecucion $record) {
                                if (! $record->hora_inicio || ! $record->hora_fin) {
                                    return 'En curso / Pendiente';
                                }
                                try {
                                    $start = Carbon::parse($record->hora_inicio);
                                    $end = Carbon::parse($record->hora_fin);

                                    return $start->diffForHumans($end, [
                                        'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                                        'parts' => 2,
                                    ]);
                                } catch (\Exception $e) {
                                    return '—';
                                }
                            }),
                    ]),

                Section::make('Personal y Recursos')
                    ->icon(Heroicon::Users)
                    ->description('Colaborador a cargo y herramientas de limpieza empleadas.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->schema([
                        TextEntry::make('colaborador')
                            ->label('Colaborador Asignado')
                            ->icon(Heroicon::User)
                            ->state(fn (LimpiezaEjecucion $record) => $record->colaborador?->persona ? ObtenerNombrePersona::desde($record->colaborador->persona) : 'Sin asignar')
                            ->placeholder('Sin asignar')
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('carrito.nombre')
                            ->label('Carrito / Bodega de Limpieza')
                            ->icon(Heroicon::Cube)
                            ->placeholder('Ninguno')
                            ->weight(FontWeight::Medium),
                    ]),

                Section::make('Insumos Consumidos')
                    ->icon(Heroicon::ShoppingBag)
                    ->description('Materiales utilizados durante el proceso de limpieza.')
                    ->schema([
                        TextEntry::make('consumos')
                            ->hiddenLabel()
                            ->html()
                            ->state(function (LimpiezaEjecucion $record) {
                                $consumos = $record->consumos;
                                if (empty($consumos)) {
                                    return '<span class="text-sm text-gray-400 dark:text-gray-500">No se registraron consumos de insumos en esta limpieza.</span>';
                                }
                                $varianteIds = array_keys($consumos);
                                $variantes = ProductoVariante::with('producto')
                                    ->whereIn('id', $varianteIds)
                                    ->get()
                                    ->keyBy('id');

                                return new HtmlString(view('components.limpieza.consumos-grid', [
                                    'consumos' => $consumos,
                                    'variantes' => $variantes,
                                ])->render());
                            }),
                    ]),

                Section::make('Novedades u Observaciones')
                    ->icon(Heroicon::ChatBubbleBottomCenterText)
                    ->description('Comentarios adicionales o incidencias reportadas.')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->hiddenLabel()
                            ->placeholder('Sin observaciones ni novedades registradas.')
                            ->weight(FontWeight::Medium),
                    ]),

                Section::make('Planificación y Horario de Origen')
                    ->icon(Heroicon::Calendar)
                    ->description('Detalles del horario y turno planificado que originaron esta ejecución.')
                    ->collapsible()
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 4,
                    ])
                    ->schema([
                        TextEntry::make('horario.turno.nombre')
                            ->label('Turno de Origen')
                            ->placeholder('No asociado a un turno planificado')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('horario.hora_estimada')
                            ->label('Hora Estimada')
                            ->placeholder('—')
                            ->icon(Heroicon::Clock),

                        TextEntry::make('horario.frecuencia')
                            ->label('Frecuencia')
                            ->badge()
                            ->color(fn (?string $state): string => $state === 'diaria' ? 'success' : 'info')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? ucfirst($state) : null)
                            ->placeholder('—'),

                        TextEntry::make('horario.dia_semana')
                            ->label('Día de la Semana')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Todos')
                            ->placeholder('Todos'),

                        TextEntry::make('horario.turno.lider')
                            ->label('Líder del Turno')
                            ->state(fn (LimpiezaEjecucion $record) => $record->horario?->turno?->lider?->persona ? ObtenerNombrePersona::desde($record->horario->turno->lider->persona) : '—')
                            ->placeholder('—'),

                        TextEntry::make('horario.turno.apoyo')
                            ->label('Apoyo del Turno')
                            ->state(fn (LimpiezaEjecucion $record) => $record->horario?->turno?->apoyo?->persona ? ObtenerNombrePersona::desde($record->horario->turno->apoyo->persona) : '—')
                            ->placeholder('—'),
                    ]),

                Section::make('Metadatos')
                    ->icon(Heroicon::InformationCircle)
                    ->description('Fechas de registro y última modificación.')
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

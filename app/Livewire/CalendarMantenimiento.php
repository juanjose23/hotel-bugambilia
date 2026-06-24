<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\Activos\EstadoMantenimiento;
use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use App\Models\Activos\ActivoMantenimiento;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;

class CalendarMantenimiento extends Widget
{
    protected string $view = 'livewire.calendar-mantenimiento';

    protected int|string|array $columnSpan = 'full';

    public function getEventsJson(): string
    {
        $events = ActivoMantenimiento::query()
            ->with(['activo.producto'])
            ->get()
            ->map(function ($mantenimiento) {
                $editUrl = ActivoMantenimientoResource::getUrl('edit', ['record' => $mantenimiento->id]);
                $color = match ($mantenimiento->estado) {
                    EstadoMantenimiento::Programado => '#3b82f6', // Programado (Azul)
                    EstadoMantenimiento::EnProceso => '#f59e0b', // En proceso (Naranja)
                    EstadoMantenimiento::Completado => '#10b981', // Completado (Verde)
                    EstadoMantenimiento::Cancelado => '#ef4444', // Cancelado (Rojo)
                };

                $activoText = 'Sin Activo';
                if ($mantenimiento->activo) {
                    $codigo = $mantenimiento->activo->codigo_inventario ?? ('ACT-'.$mantenimiento->activo->id);
                    $nombre = $mantenimiento->activo->nombre_descriptivo
                        ?? $mantenimiento->activo->producto->nombre
                        ?? 'Activo';
                    $activoText = "{$codigo} - {$nombre}";
                }

                $notasText = $mantenimiento->notas ? ' ('.Str::limit($mantenimiento->notas, 25).')' : '';
                $titleText = $activoText.$notasText;

                return [
                    'id' => $mantenimiento->id,
                    'title' => $titleText,
                    'start' => Carbon::parse($mantenimiento->fecha_programada)->toDateString(),
                    'end' => Carbon::parse($mantenimiento->fecha_programada)->toDateString(),
                    'color' => $color,
                    'textColor' => '#ffffff',
                    'url' => $editUrl,
                    'allDay' => true,
                ];
            });

        return json_encode($events) ?: '[]';
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

class ObtenerActivosPorUbicacionUseCase
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $tipoFiltro = null): array
    {
        $asignaciones = ActivoAsignacion::with([
            'activo.producto',
            'activo.moneda',
            'asignable',
        ])
            ->whereNull('fecha_fin')
            ->when($tipoFiltro, fn ($q) => $q->where('asignable_type', $tipoFiltro))
            ->get()
            ->groupBy(fn ($a) => $a->asignable_type.'|'.$a->asignable_id);

        $ubicaciones = [];

        foreach ($asignaciones as $items) {
            $first = $items->first();
            if (! $first?->asignable) {
                continue;
            }
            $tipoLabel = match ($first->asignable_type) {
                Habitacion::class => 'Habitación',
                Espacio::class => 'Espacio',
                default => 'Ubicación',
            };
            $nombreUbicacion = $first->destinoLabel();
            $activosFiltrados = $items->pluck('activo')->filter()->values();
            $subtotal = $activosFiltrados->sum(fn ($a) => (float) (($a instanceof Activo ? $a->costo_adquisicion : 0) ?? 0));
            $primerActivo = $activosFiltrados->first();
            $simbolo = $primerActivo instanceof Activo ? ($primerActivo->moneda->simbolo ?? '$') : '$';
            $ubicaciones[] = [
                'tipo' => $tipoLabel,
                'nombre' => $nombreUbicacion,
                'activos' => $activosFiltrados,
                'subtotal' => $subtotal,
                'moneda' => $simbolo,
            ];
        }

        return $ubicaciones;
    }
}

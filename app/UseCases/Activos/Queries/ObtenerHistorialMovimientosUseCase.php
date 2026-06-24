<?php

declare(strict_types=1);

namespace App\UseCases\Activos\Queries;

use App\Models\Activos\Activo;
use Illuminate\Support\Collection;

class ObtenerHistorialMovimientosUseCase
{
    /**
     * @return array{activo: Activo|null, lineaTiempo: Collection<int, array<string, mixed>>}
     */
    public function ejecutar(int $activoId): array
    {
        $query = Activo::with([
            'producto',
            'asignaciones.asignadoPor',
            'asignaciones.asignable',
            'mantenimientos.realizadoPor',
            'mantenimientos.plan.proveedor.persona',
            'bajas.creadoPor',
        ]);

        $activo = $activoId > 0
            ? $query->findOrFail($activoId)
            : $query->first();

        if ($activo === null) {
            return ['activo' => null, 'lineaTiempo' => collect()];
        }

        $lineaTiempo = collect();

        foreach ($activo->asignaciones as $asig) {
            $destino = $asig->destinoLabel();
            $lineaTiempo->push([
                'fecha' => $asig->fecha_inicio->format('d/m/Y'),
                'fecha_sort' => $asig->fecha_inicio,
                'tipo' => $asig->fecha_fin ? 'Traslado' : 'Asignación',
                'detalle' => "Asignado a: {$destino}".($asig->motivo ? " — {$asig->motivo}" : ''),
                'color' => $asig->fecha_fin ? '#f59e0b' : '#16a34a',
                'responsable' => $asig->asignadoPor?->name,
            ]);
        }

        foreach ($activo->mantenimientos as $mtto) {
            $tipoLabel = $mtto->tipo->label();
            $persona = $mtto->plan?->proveedor?->persona;
            $proveedorNombre = $persona ? $persona->primer_nombre : 'Taller interno';
            $lineaTiempo->push([
                'fecha' => $mtto->fecha_programada->format('d/m/Y'),
                'fecha_sort' => $mtto->fecha_programada,
                'tipo' => "Mantenimiento ({$tipoLabel})",
                'detalle' => "{$mtto->plan?->descripcion} — {$proveedorNombre}".($mtto->costo_real ? " (\${$mtto->costo_real})" : ''),
                'color' => '#3b82f6',
                'responsable' => $mtto->realizadoPor?->name,
            ]);
        }

        foreach ($activo->bajas as $baja) {
            $lineaTiempo->push([
                'fecha' => $baja->fecha_baja->format('d/m/Y'),
                'fecha_sort' => $baja->fecha_baja,
                'tipo' => 'Baja',
                'detalle' => "Motivo: {$baja->motivo_tipo->label()} — {$baja->motivo_detalle}",
                'color' => '#dc2626',
                'responsable' => $baja->creadoPor?->name,
            ]);
        }

        $lineaTiempo = $lineaTiempo->sortBy('fecha_sort')->values();

        return [
            'activo' => $activo,
            'lineaTiempo' => $lineaTiempo,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Activos\ActivoBaja;
use App\Repository\Models\Activos\ActivoMantenimiento;
use Illuminate\Support\Collection;

final class ObtenerHistorialMovimientosUseCase
{
    /**
     * @return array{activo: Activo|null, lineaTiempo: Collection<int, array<string, mixed>>}
     */
    public function ejecutar(int $activoId): array
    {
        $activo = $this->obtenerActivo($activoId);

        if ($activo === null) {
            return ['activo' => null, 'lineaTiempo' => collect()];
        }

        return [
            'activo' => $activo,
            'lineaTiempo' => $this->construirLineaTiempo($activo),
        ];
    }

    private function obtenerActivo(int $activoId): ?Activo
    {
        $query = Activo::with([
            'producto',
            'asignaciones.asignadoPor',
            'asignaciones.asignable',
            'mantenimientos.realizadoPor',
            'mantenimientos.plan.proveedor.persona',
            'bajas.creadoPor',
        ]);

        return $activoId > 0
            ? $query->findOrFail($activoId)
            : $query->first();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function construirLineaTiempo(Activo $activo): Collection
    {
        $lineaTiempo = collect();

        foreach ($activo->asignaciones as $asig) {
            $lineaTiempo->push($this->entradaAsignacion($asig));
        }

        foreach ($activo->mantenimientos as $mtto) {
            $lineaTiempo->push($this->entradaMantenimiento($mtto));
        }

        foreach ($activo->bajas as $baja) {
            $lineaTiempo->push($this->entradaBaja($baja));
        }

        return $lineaTiempo->sortBy('fecha_sort')->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function entradaAsignacion(ActivoAsignacion $asig): array
    {
        $destino = $asig->destinoLabel();

        return [
            'fecha' => $asig->fecha_inicio->format('d/m/Y'),
            'fecha_sort' => $asig->fecha_inicio,
            'tipo' => $asig->fecha_fin ? 'Traslado' : 'Asignación',
            'detalle' => "Asignado a: $destino".($asig->motivo ? " — $asig->motivo" : ''),
            'color' => $asig->fecha_fin ? '#f59e0b' : '#16a34a',
            'responsable' => $asig->asignadoPor?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function entradaMantenimiento(ActivoMantenimiento $mtto): array
    {
        $tipoLabel = $mtto->tipo->label();
        $persona = $mtto->plan?->proveedor?->persona;
        $proveedorNombre = $persona ? $persona->primer_nombre : 'Taller interno';

        return [
            'fecha' => $mtto->fecha_programada->format('d/m/Y'),
            'fecha_sort' => $mtto->fecha_programada,
            'tipo' => "Mantenimiento ($tipoLabel)",
            'detalle' => "{$mtto->plan?->descripcion} — $proveedorNombre".($mtto->costo_real ? " (\$$mtto->costo_real)" : ''),
            'color' => '#3b82f6',
            'responsable' => $mtto->realizadoPor?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function entradaBaja(ActivoBaja $baja): array
    {
        return [
            'fecha' => $baja->fecha_baja->format('d/m/Y'),
            'fecha_sort' => $baja->fecha_baja,
            'tipo' => 'Baja',
            'detalle' => "Motivo: {$baja->motivo_tipo->label()} — $baja->motivo_detalle",
            'color' => '#dc2626',
            'responsable' => $baja->creadoPor?->name,
        ];
    }
}

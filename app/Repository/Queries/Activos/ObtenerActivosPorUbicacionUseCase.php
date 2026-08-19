<?php

declare(strict_types=1);

namespace App\Repository\Queries\Activos;

use App\BusinessLogic\Monedas\ConvertirMoneda;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class ObtenerActivosPorUbicacionUseCase
{
    public function __construct(
        private readonly ConvertirMoneda $convertirMoneda
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $tipoFiltro = null): array
    {
        $asignaciones = $this->consultarAsignaciones($tipoFiltro);

        $ubicaciones = [];

        foreach ($asignaciones as $items) {
            $first = $items->first();
            if (! $first?->asignable) {
                continue;
            }

            $ubicaciones[] = $this->construirEntradaUbicacion($first, $items);
        }

        return $ubicaciones;
    }

    /**
     * @return Collection<int|string, EloquentCollection<int, ActivoAsignacion>>
     */
    private function consultarAsignaciones(?string $tipoFiltro): Collection
    {
        return ActivoAsignacion::with([
            'activo.producto',
            'activo.moneda',
            'asignable',
            'asignadoPor',
        ])
            ->whereNull('fecha_fin')
            ->when($tipoFiltro, fn ($q) => $q->where('asignable_type', $tipoFiltro))
            ->get()
            ->groupBy(fn ($a) => $a->asignable_type.'|'.$a->asignable_id);
    }

    /**
     * @param  Collection<int, ActivoAsignacion>  $items
     * @return array<string, mixed>
     */
    private function construirEntradaUbicacion(ActivoAsignacion $first, Collection $items): array
    {
        $activosFiltrados = $items->pluck('activo')->filter()->values();
        $subtotal = $activosFiltrados->sum(fn ($a) => (float) ($a instanceof Activo ? $this->convertirMoneda->aBase((float) ($a->costo_adquisicion ?? 0), $a->moneda_id) : 0));

        return [
            'tipo' => $this->etiquetaTipo($first->asignable_type),
            'nombre' => $first->destinoLabel(),
            'activos' => $activosFiltrados,
            'subtotal' => $subtotal,
            'moneda' => 'C$',
        ];
    }

    private function etiquetaTipo(string $tipo): string
    {
        return match ($tipo) {
            Habitacion::class => 'Habitación',
            Espacio::class => 'Espacio',
            default => 'Ubicación',
        };
    }
}

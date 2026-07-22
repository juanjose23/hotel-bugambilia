<?php

declare(strict_types=1);

namespace App\Actions\Activos\Concerns;

use App\BusinessLogic\Activos\Data\ActivoFiltrosData;
use App\Enums\Activos\EstadoActivo;
use App\Repository\Models\Activos\Activo;
use Illuminate\Database\Eloquent\Builder;

trait FiltrosActivos
{
    /**
     * @param  Builder<Activo>  $query
     * @return Builder<Activo>
     */
    protected function aplicarFiltrosQuery(
        Builder $query,
        ActivoFiltrosData $filtros,
    ): Builder {
        if ($filtros->productoId !== null) {
            $query->where('producto_id', $filtros->productoId);
        }

        if ($filtros->estado !== null) {
            $query->where('estado', $filtros->estado);
        }

        if ($filtros->ubicacionTipo !== null) {
            $tipo = $filtros->ubicacionTipo;
            $query->whereHas('asignacionActiva', function ($q) use ($tipo): void {
                $q->where('asignable_type', $tipo);
            });
        }

        return $query;
    }

    /**
     * @return array<int, array{label: string, valor: string}>
     */
    protected function prepararFiltros(
        ActivoFiltrosData $filtros,
    ): array {
        $estado = $filtros->estado !== null
            ? EstadoActivo::tryFrom($filtros->estado)?->label()
            : null;

        return [
            [
                'label' => 'Estado',
                'valor' => $estado ?? 'TODOS',
            ],
            [
                'label' => 'Ubicación',
                'valor' => $filtros->ubicacionTipo ?? 'TODOS',
            ],
        ];
    }
}

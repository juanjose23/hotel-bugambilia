<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\Repository\Models\Inventario\MovimientoStock;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * HTB-INV-003 — Movimientos de Inventario
 * Historial de todos los movimientos en un período (entradas, salidas, traslados, ajustes, devoluciones).
 */
class ObtenerMovimientosInventario
{
    /**
     * @param  array{tipo?: string|null, producto_id?: int|null, lote_id?: int|null, fecha_desde?: Carbon|string|null, fecha_hasta?: Carbon|string|null}  $filtros
     * @return LengthAwarePaginator<int, MovimientoStock>
     */
    public function ejecutar(array $filtros = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->aplicarFiltros($this->consultaBase(), $filtros)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @return Builder<MovimientoStock>
     */
    private function consultaBase(): Builder
    {
        return MovimientoStock::query()
            ->with([
                'lote:id,codigo_lote,costo_unitario',
                'producto:id,nombre',
                'ubicacionOrigen:id,nombre',
                'ubicacionDestino:id,nombre',
            ]);
    }

    /**
     * @param  Builder<MovimientoStock>  $query
     * @param  array{tipo?: string|null, producto_id?: int|null, lote_id?: int|null, fecha_desde?: Carbon|string|null, fecha_hasta?: Carbon|string|null}  $filtros
     * @return Builder<MovimientoStock>
     */
    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        return $query
            ->when(
                isset($filtros['tipo']) && $filtros['tipo'],
                fn ($q) => $q->where('tipo', $filtros['tipo'])
            )
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->when(
                isset($filtros['lote_id']) && $filtros['lote_id'],
                fn ($q) => $q->where('lote_id', $filtros['lote_id'])
            )
            ->when(
                isset($filtros['fecha_desde']) && $filtros['fecha_desde'],
                fn ($q) => $q->where('created_at', '>=', Carbon::parse($filtros['fecha_desde'])->startOfDay())
            )
            ->when(
                isset($filtros['fecha_hasta']) && $filtros['fecha_hasta'],
                fn ($q) => $q->where('created_at', '<=', Carbon::parse($filtros['fecha_hasta'])->endOfDay())
            );
    }
}

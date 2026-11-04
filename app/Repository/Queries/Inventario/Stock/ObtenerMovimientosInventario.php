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
                $filtros['tipo'] ?? null,
                fn ($q, $tipo) => $q->where('tipo', $tipo)
            )
            ->when(
                $filtros['producto_id'] ?? null,
                fn ($q, $productoId) => $q->where('producto_id', $productoId)
            )
            ->when(
                $filtros['lote_id'] ?? null,
                fn ($q, $loteId) => $q->where('lote_id', $loteId)
            )
            ->when(
                $filtros['fecha_desde'] ?? null,
                fn ($q, $fechaDesde) => $q->where('created_at', '>=', Carbon::parse($fechaDesde)->startOfDay())
            )
            ->when(
                $filtros['fecha_hasta'] ?? null,
                fn ($q, $fechaHasta) => $q->where('created_at', '<=', Carbon::parse($fechaHasta)->endOfDay())
            );
    }
}

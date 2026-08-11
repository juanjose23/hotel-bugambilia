<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\BusinessLogic\Inventario\Data\Stock\StockProductoData;
use App\Enums\Inventario\EstadoLote;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * HTB-INV-001 — Stock Actual por Producto
 * Retorna la cantidad total disponible por producto, desglosada por ubicación.
 */
class ObtenerStockPorProducto
{
    /**
     * @param  array{producto_id?: int|null, ubicacion_id?: int|null}  $filtros
     * @return Collection<int, StockProductoData>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return $this->consultaBase($filtros)
            ->get()
            ->map(fn ($row) => new StockProductoData(
                productoId: (int) $row->producto_id,
                producto: $row->producto,
                variante: $row->variante,
                categoria: $row->categoria,
                ubicacionId: (int) $row->ubicacion_id,
                ubicacion: $row->ubicacion,
                stockDisponible: (float) $row->stock_disponible,
                stockCuarentena: (float) $row->stock_cuarentena,
                totalLotes: (int) $row->total_lotes,
            ));
    }

    /**
     * @param  array{producto_id?: int|null, ubicacion_id?: int|null}  $filtros
     */
    private function consultaBase(array $filtros): Builder
    {
        return DB::table('inv_lotes as l')
            ->join('productos as p', 'l.producto_id', '=', 'p.id')
            ->join('ubicaciones as u', 'l.ubicacion_id', '=', 'u.id')
            ->leftJoin('producto_variantes as pv', 'l.producto_variante_id', '=', 'pv.id')
            ->leftJoin('catalogos as cat', 'p.categoria_id', '=', 'cat.id')
            ->whereNull('l.deleted_at')
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('l.producto_id', $filtros['producto_id'])
            )
            ->when(
                isset($filtros['ubicacion_id']) && $filtros['ubicacion_id'],
                fn ($q) => $q->where('l.ubicacion_id', $filtros['ubicacion_id'])
            )
            ->select([
                'p.id as producto_id',
                'p.nombre as producto',
                'pv.nombre_variante as variante',
                'cat.nombre as categoria',
                'u.id as ubicacion_id',
                'u.nombre as ubicacion',
                DB::raw('SUM(CASE WHEN l.estado = '.EstadoLote::Disponible->value.' THEN l.cantidad_disponible ELSE 0 END) as stock_disponible'),
                DB::raw('SUM(CASE WHEN l.estado = '.EstadoLote::Cuarentena->value.' THEN l.cantidad_disponible ELSE 0 END) as stock_cuarentena'),
                DB::raw('COUNT(l.id) as total_lotes'),
            ])
            ->groupBy('p.id', 'p.nombre', 'pv.nombre_variante', 'cat.nombre', 'u.id', 'u.nombre')
            ->orderBy('p.nombre');
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\BusinessLogic\Inventario\Data\Stock\ValorizacionCategoriaData;
use App\Enums\Inventario\EstadoLote;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * HTB-INV-007 — Valorización del Inventario
 * Calcula el valor monetario del stock disponible (cantidad × costo unitario).
 * El costo unitario se toma del precio unitario de la orden de compra de origen del lote.
 */
class ObtenerValorizacionInventario
{
    /**
     * @param  array{ubicacion_id?: int|null, producto_id?: int|null}  $filtros
     * @return Collection<int, ValorizacionCategoriaData>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return $this->consultaBase($filtros)
            ->get()
            ->map(fn ($row) => new ValorizacionCategoriaData(
                productoId: (int) $row->producto_id,
                producto: $row->producto,
                categoria: $row->categoria,
                ubicacion: $row->ubicacion,
                stockTotal: (float) $row->stock_total,
                costoPromedio: (float) $row->costo_promedio,
                valorTotal: (float) $row->valor_total,
            ));
    }

    /**
     * Retorna el gran total de valorización.
     *
     * @param  array{ubicacion_id?: int|null}  $filtros
     */
    public function totalGeneral(array $filtros = []): float
    {
        /** @var int|float $total */
        $total = $this->ejecutar($filtros)->sum('valorTotal');

        return (float) $total;
    }

    /**
     * @param  array{ubicacion_id?: int|null, producto_id?: int|null}  $filtros
     */
    private function consultaBase(array $filtros): Builder
    {
        return DB::table('inv_lotes as l')
            ->join('productos as p', 'l.producto_id', '=', 'p.id')
            ->join('ubicaciones as u', 'l.ubicacion_id', '=', 'u.id')
            ->leftJoin('catalogos as cat', 'p.categoria_id', '=', 'cat.id')
            ->leftJoin('recepcion_items as ri', 'l.recepcion_item_id', '=', 'ri.id')
            ->leftJoin('orden_compra_items as oci', 'ri.orden_item_id', '=', 'oci.id')
            ->leftJoin('ordenes_compra as oc', 'oci.orden_compra_id', '=', 'oc.id')
            ->whereNull('l.deleted_at')
            ->where('l.estado', EstadoLote::Disponible->value)
            ->where('l.cantidad_disponible', '>', 0)
            ->when(
                isset($filtros['ubicacion_id']) && $filtros['ubicacion_id'],
                fn ($q) => $q->where('l.ubicacion_id', $filtros['ubicacion_id'])
            )
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('l.producto_id', $filtros['producto_id'])
            )
            ->select([
                'p.id as producto_id',
                'p.nombre as producto',
                'cat.nombre as categoria',
                'u.nombre as ubicacion',
                DB::raw('SUM(l.cantidad_disponible) as stock_total'),
                DB::raw('AVG(COALESCE(l.costo_unitario, 0)) as costo_promedio'),
                DB::raw('SUM(l.cantidad_disponible * COALESCE(l.costo_unitario, 0)) as valor_total'),
            ])
            ->groupBy('p.id', 'p.nombre', 'cat.nombre', 'u.nombre')
            ->orderBy('valor_total', 'desc');
    }
}

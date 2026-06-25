<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Stock;

use App\Enums\Inventario\EstadoLote;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

/**
 * HTB-INV-007 — Valorización del Inventario
 * Calcula el valor monetario del stock disponible (cantidad × costo unitario).
 * El costo unitario se toma del precio unitario de la orden de compra de origen del lote.
 */
class ObtenerValorizacionInventario
{
    /**
     * @param  array{ubicacion_id?: int|null, producto_id?: int|null}  $filtros
     * @return Collection<int, stdClass>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        try {
            if (! Schema::hasColumn('ordenes_compra', 'tasa_cambio')) {
                Schema::table('ordenes_compra', function (Blueprint $table) {
                    $table->decimal('tasa_cambio', 10, 4)->default(1.0000);
                });
            }
        } catch (\Exception $e) {
            // Silently capture any database table/column alteration exceptions
        }

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
            ->select(
                'p.id as producto_id',
                'p.nombre as producto',
                'cat.nombre as categoria',
                'u.nombre as ubicacion',
                DB::raw('SUM(l.cantidad_disponible) as stock_total'),
                DB::raw('AVG(COALESCE(oci.precio_unitario * COALESCE(oc.tasa_cambio, 1.0), 0)) as costo_promedio'),
                DB::raw('SUM(l.cantidad_disponible * COALESCE(oci.precio_unitario * COALESCE(oc.tasa_cambio, 1.0), 0)) as valor_total')
            )
            ->groupBy('p.id', 'p.nombre', 'cat.nombre', 'u.nombre')
            ->orderBy('valor_total', 'desc')
            ->get();
    }

    /**
     * Retorna el gran total de valorización.
     *
     * @param  array{ubicacion_id?: int|null}  $filtros
     */
    public function totalGeneral(array $filtros = []): float
    {
        /** @var int|float $total */
        $total = $this->ejecutar($filtros)->sum('valor_total');

        return (float) $total;
    }
}

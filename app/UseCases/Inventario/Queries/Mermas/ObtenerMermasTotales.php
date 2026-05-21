<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Mermas;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * HTB-INV-009 — Mermas Totales (Pérdidas)
 * Resumen de pérdidas por caducidad, rechazos y daños con costo asociado.
 */
class ObtenerMermasTotales
{
    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null}  $filtros
     * @return Collection<int, stdClass>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        $tiposBaja = [
            'MOV_AJUSTE' => 'Ajuste de Inventario',
        ];

        return DB::table('inv_movimientos as m')
            ->join('productos as p', 'm.producto_id', '=', 'p.id')
            ->leftJoin('recepcion_items as ri', function ($join) {
                $join->on('m.documento_id', '=', 'ri.id')
                    ->where('m.documento_tipo', '=', 'recepcion_item');
            })
            ->leftJoin('orden_compra_items as oci', 'ri.orden_item_id', '=', 'oci.id')
            ->whereIn('m.tipo', array_keys($tiposBaja))
            ->when(
                isset($filtros['periodo_desde']) && $filtros['periodo_desde'],
                fn ($q) => $q->where('m.created_at', '>=', Carbon::parse($filtros['periodo_desde'])->startOfDay())
            )
            ->when(
                isset($filtros['periodo_hasta']) && $filtros['periodo_hasta'],
                fn ($q) => $q->where('m.created_at', '<=', Carbon::parse($filtros['periodo_hasta'])->endOfDay())
            )
            ->select(
                'm.tipo as tipo_movimiento',
                'm.referencia',
                'p.nombre as producto',
                DB::raw('SUM(m.cantidad) as cantidad_perdida'),
                DB::raw('AVG(COALESCE(oci.precio_unitario, 0)) as costo_unitario'),
                DB::raw('SUM(m.cantidad * COALESCE(oci.precio_unitario, 0)) as perdida_total')
            )
            ->groupBy('m.tipo', 'm.referencia', 'p.nombre')
            ->orderBy('perdida_total', 'desc')
            ->get()
            ->map(function ($row) {
                $ref = strtolower($row->referencia ?? '');
                if (str_contains($ref, 'vencimiento') || str_contains($ref, 'caducidad')) {
                    $row->categoria = 'Caducidad';
                } elseif (str_contains($ref, 'rechazo')) {
                    $row->categoria = 'Calidad / Rechazo';
                } else {
                    $row->categoria = 'Ajuste Manual';
                }

                return $row;
            });
    }

    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null}  $filtros
     */
    public function totalPerdidas(array $filtros = []): float
    {
        return (float) $this->ejecutar($filtros)->sum('perdida_total');
    }
}

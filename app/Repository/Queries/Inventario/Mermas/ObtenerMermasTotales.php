<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Mermas;

use App\BusinessLogic\Inventario\Data\Mermas\MermaDetalleData;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * HTB-INV-009 — Mermas Totales (Pérdidas)
 * Resumen de pérdidas por caducidad, rechazos y daños con costo asociado.
 */
class ObtenerMermasTotales
{
    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null}  $filtros
     * @return Collection<int, MermaDetalleData>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return $this->consultaBase($filtros)
            ->get()
            ->map(function ($row) {
                $ref = strtolower($row->referencia ?? '');

                return new MermaDetalleData(
                    tipoMovimiento: $row->tipo_movimiento,
                    referencia: $row->referencia,
                    producto: $row->producto,
                    cantidadPerdida: (float) $row->cantidad_perdida,
                    costoUnitario: (float) $row->costo_unitario,
                    perdidaTotal: (float) $row->perdida_total,
                    categoria: $this->resolverCategoria($ref),
                );
            });
    }

    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null}  $filtros
     */
    public function totalPerdidas(array $filtros = []): float
    {
        $sum = $this->ejecutar($filtros)->sum('perdidaTotal');

        return is_numeric($sum) ? (float) $sum : 0.0;
    }

    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null}  $filtros
     */
    private function consultaBase(array $filtros): Builder
    {
        $tiposBaja = [
            'MOV_AJUSTE' => 'Ajuste de Inventario',
            'CONSUMO' => 'Consumo de Cocina',
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
            ->select([
                'm.tipo as tipo_movimiento',
                'm.referencia',
                'p.nombre as producto',
                DB::raw('SUM(m.cantidad) as cantidad_perdida'),
                DB::raw('AVG(COALESCE(oci.precio_unitario, 0)) as costo_unitario'),
                DB::raw('SUM(m.cantidad * COALESCE(oci.precio_unitario, 0)) as perdida_total'),
            ])
            ->groupBy('m.tipo', 'm.referencia', 'p.nombre')
            ->orderBy('perdida_total', 'desc');
    }

    private function resolverCategoria(string $referencia): string
    {
        return match (true) {
            str_contains($referencia, 'vencimiento') || str_contains($referencia, 'caducidad') => 'Caducidad',
            str_contains($referencia, 'rechazo') => 'Calidad / Rechazo',
            default => 'Ajuste Manual',
        };
    }
}

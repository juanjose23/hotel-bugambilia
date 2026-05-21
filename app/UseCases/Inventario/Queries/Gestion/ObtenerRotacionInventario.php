<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Gestion;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * HTB-INV-008 — Rotación de Inventario
 * Productos con mayor y menor rotación basada en movimientos de salida en los últimos N meses.
 */
class ObtenerRotacionInventario
{
    /**
     * @param  array{meses?: int}  $filtros
     * @return Collection<int, stdClass>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        $meses = isset($filtros['meses']) && $filtros['meses'] > 0 ? (int) $filtros['meses'] : 3;
        $fechaDesde = now()->subMonths($meses)->startOfDay();

        $tiposSalida = ['MOV_SALIDA', 'MOV_AJUSTE'];

        $salidas = DB::table('inv_movimientos as m')
            ->join('productos as p', 'm.producto_id', '=', 'p.id')
            ->whereIn('m.tipo', $tiposSalida)
            ->where('m.created_at', '>=', $fechaDesde)
            ->select(
                'p.id as producto_id',
                'p.nombre as producto',
                DB::raw('SUM(m.cantidad) as total_salidas')
            )
            ->groupBy('p.id', 'p.nombre');

        $stockPromedio = DB::table('inv_lotes as l')
            ->join('productos as p', 'l.producto_id', '=', 'p.id')
            ->whereNull('l.deleted_at')
            ->select(
                'p.id as producto_id',
                DB::raw('AVG(l.cantidad_disponible) as stock_promedio')
            )
            ->groupBy('p.id');

        // Combinar: traemos salidas y le unimos el stock promedio
        $resultado = DB::table(DB::raw('('.$salidas->toSql().') as sal'))
            ->mergeBindings($salidas)
            ->leftJoinSub($stockPromedio, 'sp', 'sal.producto_id', '=', 'sp.producto_id')
            ->select(
                'sal.producto',
                'sal.total_salidas',
                DB::raw('COALESCE(sp.stock_promedio, 0) as stock_promedio'),
                DB::raw('CASE WHEN COALESCE(sp.stock_promedio, 0) > 0 THEN ROUND((sal.total_salidas / sp.stock_promedio)::numeric, 2) ELSE 0 END as indice_rotacion')
            )
            ->orderBy('indice_rotacion', 'desc')
            ->get();

        // Clasificar según índice de rotación
        return $resultado->map(function ($row) {
            $row->clasificacion = match (true) {
                $row->indice_rotacion >= 2.0 => 'Alta',
                $row->indice_rotacion >= 0.5 => 'Media',
                default => 'Baja',
            };

            return $row;
        });
    }
}

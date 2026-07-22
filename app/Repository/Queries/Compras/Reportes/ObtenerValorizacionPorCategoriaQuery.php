<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\ValorizacionCategoriaReporteData;
use Illuminate\Support\Facades\DB;

final class ObtenerValorizacionPorCategoriaQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): ValorizacionCategoriaReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $data = DB::table('orden_compra_items as oci')
            ->join('ordenes_compra as oc', 'oci.orden_compra_id', '=', 'oc.id')
            ->join('productos as p', 'oci.producto_id', '=', 'p.id')
            ->leftJoin('catalogos as cat', 'p.categoria_id', '=', 'cat.id')
            ->select(
                DB::raw("coalesce(cat.nombre, 'Sin Categoría') as categoria"),
                DB::raw('count(distinct oc.id) as total_ordenes'),
                DB::raw('sum(oci.subtotal) as total_invertido')
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->groupBy('cat.id', 'cat.nombre')
            ->orderByDesc('total_invertido')
            ->get();

        $sumResult = $data->sum('total_invertido');
        $totalGeneral = is_numeric($sumResult) ? (float) $sumResult : 0.0;

        $dataConPorcentaje = $data->map(function ($item) use ($totalGeneral) {
            $item->porcentaje = $totalGeneral > 0 ? round(($item->total_invertido / $totalGeneral) * 100, 1) : 0;

            return $item;
        })->toArray();

        return new ValorizacionCategoriaReporteData(
            data: $dataConPorcentaje,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
            totalGeneral: $totalGeneral,
        );
    }
}

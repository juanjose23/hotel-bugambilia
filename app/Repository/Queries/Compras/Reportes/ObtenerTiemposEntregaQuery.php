<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\TiemposEntregaReporteData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ObtenerTiemposEntregaQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): TiemposEntregaReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $records = DB::table('recepciones_compra as rc')
            ->join('ordenes_compra as oc', 'rc.orden_compra_id', '=', 'oc.id')
            ->join('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->select(
                'prov.id as proveedor_id',
                DB::raw("coalesce(obtener_nombre_completo(prov.persona_id), 'Sin Proveedor') as proveedor_nombre"),
                'oc.fecha_orden',
                'rc.fecha_recepcion'
            )
            ->whereNull('rc.deleted_at')
            ->whereNull('oc.deleted_at')
            ->whereBetween('rc.fecha_recepcion', [$fechaInicio, $fechaFin])
            ->get();

        $grouped = $records->groupBy('proveedor_id')->map(function ($items) {
            $totalDias = 0;
            $count = $items->count();

            foreach ($items as $item) {
                $fechaOrden = Carbon::parse($item->fecha_orden);
                $fechaRecepcion = Carbon::parse($item->fecha_recepcion);
                $totalDias += $fechaOrden->diffInDays($fechaRecepcion);
            }

            return (object) [
                'proveedor_nombre' => (string) ($items->first()->proveedor_nombre ?? ''),
                'ordenes_recibidas' => $count,
                'promedio_dias' => $count > 0 ? round($totalDias / $count, 1) : 0,
            ];
        })->values()->toArray();

        return new TiemposEntregaReporteData(
            data: $grouped,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y')
        );
    }
}

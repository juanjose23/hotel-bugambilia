<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\SeguimientoOrdenCompraReporteData;
use Illuminate\Support\Facades\DB;

final class ObtenerSeguimientoOrdenesCompraQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): SeguimientoOrdenCompraReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $data = DB::table('ordenes_compra as oc')
            ->leftJoin('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->leftJoin('solicitudes_compra as sc', 'oc.solicitud_id', '=', 'sc.id')
            ->leftJoin('recepciones_compra as rc', 'oc.id', '=', 'rc.orden_compra_id')
            ->select(
                'oc.id as orden_id',
                'oc.codigo',
                'oc.fecha_orden',
                'oc.estado',
                'oc.total as monto_total',
                DB::raw("coalesce(obtener_nombre_completo(prov.persona_id), 'Sin Proveedor') as proveedor_nombre"),
                'sc.codigo as solicitud_codigo',
                'rc.fecha_recepcion'
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->orderBy('oc.fecha_orden')
            ->get();

        $dataAgrupada = $data->groupBy('orden_id')->map(function ($items) {
            $first = $items->first();
            if ($first === null) {
                return (object) [
                    'codigo' => '',
                    'fecha_orden' => '',
                    'estado' => '',
                    'monto_total' => 0.0,
                    'proveedor' => '',
                    'solicitud' => '',
                    'recepcion_count' => 0,
                ];
            }
            $recepcionCount = $items->where('fecha_recepcion', '!=', null)->count();

            return (object) [
                'codigo' => $first->codigo,
                'fecha_orden' => $first->fecha_orden,
                'estado' => $first->estado,
                'monto_total' => (float) $first->monto_total,
                'proveedor' => $first->proveedor_nombre,
                'solicitud' => $first->solicitud_codigo,
                'recepcion_count' => $recepcionCount,
            ];
        })->values()->toArray();

        return new SeguimientoOrdenCompraReporteData(
            data: $dataAgrupada,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
        );
    }
}

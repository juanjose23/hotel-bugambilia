<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\DevolucionesProveedorReporteData;
use Illuminate\Support\Facades\DB;

final class ObtenerDevolucionesPorProveedorQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): DevolucionesProveedorReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $data = DB::table('devoluciones_compra as dc')
            ->join('ordenes_compra as oc', 'dc.orden_compra_id', '=', 'oc.id')
            ->join('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->select(
                'dc.id as devolucion_id',
                'dc.codigo',
                'dc.estado',
                'dc.fecha_devolucion',
                'dc.motivo',
                'oc.codigo as orden_codigo',
                DB::raw('obtener_nombre_completo(prov.persona_id::int) as proveedor_nombre')
            )
            ->whereNull('dc.deleted_at')
            ->whereNull('oc.deleted_at')
            ->whereBetween('dc.fecha_devolucion', [$fechaInicio, $fechaFin])
            ->orderBy('proveedor_nombre')
            ->orderByDesc('dc.fecha_devolucion')
            ->get();

        $dataArr = $data->toArray();

        return new DevolucionesProveedorReporteData(
            data: $dataArr,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
            totalDevoluciones: $data->count(),
        );
    }
}

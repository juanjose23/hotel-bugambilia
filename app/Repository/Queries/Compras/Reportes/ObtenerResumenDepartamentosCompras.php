<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\ResumenDepartamentosReporteData;
use Illuminate\Support\Facades\DB;

final class ObtenerResumenDepartamentosCompras
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): ResumenDepartamentosReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $data = DB::table('ordenes_compra as oc')
            ->join('solicitudes_compra as s', 'oc.solicitud_id', '=', 's.id')
            ->join('catalogos as c', 's.departamento_solicitante_id', '=', 'c.id')
            ->select(
                'c.nombre as departamento',
                DB::raw('count(oc.id) as conteo_ordenes'),
                DB::raw('sum(oc.total) as total_oc')
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->groupBy('c.id', 'c.nombre')
            ->get();

        $dataArr = $data->toArray();

        return new ResumenDepartamentosReporteData(
            data: $dataArr,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y')
        );
    }
}

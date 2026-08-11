<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\ResumenDepartamentosReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class ObtenerResumenDepartamentosCompras
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): ResumenDepartamentosReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $dataArr = $this->consultarResumen($fechaInicio, $fechaFin);

        return new ResumenDepartamentosReporteData(
            data: $dataArr,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y')
        );
    }

    /**
     * @return array{CarbonInterface, CarbonInterface}
     */
    private function resolverRangoFechas(?string $fechaInicioStr, ?string $fechaFinStr): array
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, now()->startOfMonth());
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        return [$fechaInicio, $fechaFin];
    }

    /**
     * @return array<int, mixed>
     */
    private function consultarResumen(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): array
    {
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

        return $data->toArray();
    }
}

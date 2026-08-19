<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\RotacionComprasReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class ObtenerRotacionComprasQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): RotacionComprasReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $dataArr = $this->consultarRotacion($fechaInicio, $fechaFin);

        return new RotacionComprasReporteData(
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
    private function consultarRotacion(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): array
    {
        $data = DB::table('orden_compra_items as oci')
            ->join('ordenes_compra as oc', 'oci.orden_compra_id', '=', 'oc.id')
            ->join('productos as p', 'oci.producto_id', '=', 'p.id')
            ->leftJoin('producto_variantes as pv', 'oci.producto_variante_id', '=', 'pv.id')
            ->select(
                'p.nombre as producto_nombre',
                'pv.codigo as variante_codigo',
                DB::raw('sum(oci.cantidad) as total_cantidad'),
                DB::raw('sum(oci.subtotal) as total_costo')
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->groupBy('p.id', 'p.nombre', 'pv.id', 'pv.codigo')
            ->orderByDesc('total_cantidad')
            ->get();

        return $data->toArray();
    }
}

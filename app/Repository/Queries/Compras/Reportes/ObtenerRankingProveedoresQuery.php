<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\RankingProveedoresReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ObtenerRankingProveedoresQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): RankingProveedoresReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $ordenes = $this->consultarOrdenes($fechaInicio, $fechaFin);
        $devoluciones = $this->consultarDevoluciones($fechaInicio, $fechaFin);
        $ranked = $this->combinarRanking($ordenes, $devoluciones);

        return new RankingProveedoresReporteData(
            data: $ranked,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
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
     * @return Collection<int, stdClass>
     */
    private function consultarOrdenes(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): Collection
    {
        return DB::table('ordenes_compra as oc')
            ->join('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->leftJoin('recepciones_compra as rc', function ($join) {
                $join->on('rc.orden_compra_id', '=', 'oc.id')
                    ->whereNull('rc.deleted_at');
            })
            ->select(
                'prov.id as proveedor_id',
                DB::raw('obtener_nombre_completo(prov.persona_id::int) as proveedor_nombre'),
                DB::raw('count(distinct oc.id) as total_ordenes'),
                DB::raw('count(distinct rc.id) as ordenes_recibidas'),
                DB::raw('sum(oc.total) as monto_total'),
                DB::raw('avg(extract(epoch from rc.fecha_recepcion) - extract(epoch from oc.fecha_orden)) / 86400 as promedio_dias_entrega')
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->whereNotNull('oc.proveedor_id')
            ->groupBy('prov.id')
            ->get();
    }

    /**
     * @return Collection<int|string, stdClass>
     */
    private function consultarDevoluciones(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): Collection
    {
        return DB::table('devoluciones_compra as dc')
            ->join('ordenes_compra as oc', 'dc.orden_compra_id', '=', 'oc.id')
            ->whereNull('dc.deleted_at')
            ->whereNull('oc.deleted_at')
            ->whereBetween('dc.created_at', [$fechaInicio, $fechaFin])
            ->select('oc.proveedor_id', DB::raw('count(*) as total_devoluciones'))
            ->groupBy('oc.proveedor_id')
            ->get()
            ->keyBy('proveedor_id');
    }

    /**
     * @param  Collection<int, stdClass>  $ordenes
     * @param  Collection<int|string, stdClass>  $devoluciones
     * @return array<int, mixed>
     */
    private function combinarRanking(Collection $ordenes, Collection $devoluciones): array
    {
        return $ordenes->map(function ($item) use ($devoluciones) {
            $dev = $devoluciones->get($item->proveedor_id);
            $totalDevoluciones = $dev ? $dev->total_devoluciones : 0;
            $item->total_devoluciones = $totalDevoluciones;
            $item->porcentaje_devoluciones = $item->ordenes_recibidas > 0
                ? round(($totalDevoluciones / $item->ordenes_recibidas) * 100, 1)
                : 0;
            $item->promedio_dias_entrega = round((float) ($item->promedio_dias_entrega ?? 0), 1);

            return $item;
        })->sortBy('promedio_dias_entrega')->values()->toArray();
    }
}

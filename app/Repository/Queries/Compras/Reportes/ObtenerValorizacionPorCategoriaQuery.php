<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\ValorizacionCategoriaReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ObtenerValorizacionPorCategoriaQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): ValorizacionCategoriaReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $data = $this->consultarDatos($fechaInicio, $fechaFin);
        $totalGeneral = $this->calcularTotalGeneral($data);
        $dataConPorcentaje = $this->agregarPorcentaje($data, $totalGeneral);

        return new ValorizacionCategoriaReporteData(
            data: $dataConPorcentaje,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
            totalGeneral: $totalGeneral,
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
    private function consultarDatos(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): Collection
    {
        return DB::table('orden_compra_items as oci')
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
    }

    /**
     * @param  Collection<int, stdClass>  $data
     */
    private function calcularTotalGeneral(Collection $data): float
    {
        $sumResult = $data->sum('total_invertido');

        return is_numeric($sumResult) ? (float) $sumResult : 0.0;
    }

    /**
     * @param  Collection<int, stdClass>  $data
     * @return array<int, mixed>
     */
    private function agregarPorcentaje(Collection $data, float $totalGeneral): array
    {
        return $data->map(function ($item) use ($totalGeneral) {
            $item->porcentaje = $totalGeneral > 0 ? round(($item->total_invertido / $totalGeneral) * 100, 1) : 0;

            return $item;
        })->toArray();
    }
}

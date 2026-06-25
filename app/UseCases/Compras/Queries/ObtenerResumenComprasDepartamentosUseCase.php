<?php

declare(strict_types=1);

namespace App\UseCases\Compras\Queries;

use App\DTOs\Compras\ResumenComprasDepartamentosFiltro;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ObtenerResumenComprasDepartamentosUseCase
{
    /**
     * @param  ResumenComprasDepartamentosFiltro|array<string, mixed>  $filtros
     * @return array{data: Collection<int, \stdClass>, fechaInicio: Carbon, fechaFin: Carbon}
     */
    public function execute(ResumenComprasDepartamentosFiltro|array $filtros): array
    {
        if (is_array($filtros)) {
            $filtros = ResumenComprasDepartamentosFiltro::fromArray($filtros);
        }

        try {
            $fechaInicio = $filtros->fechaInicio
                ? Carbon::createFromFormat('Y-m-d', $filtros->fechaInicio)?->startOfDay() ?? now()->startOfMonth()
                : now()->startOfMonth();

            $fechaFin = $filtros->fechaFin
                ? Carbon::createFromFormat('Y-m-d', $filtros->fechaFin)?->endOfDay() ?? now()
                : now();

            if ($fechaInicio->gt($fechaFin)) {
                $temp = $fechaInicio;
                $fechaInicio = $fechaFin->copy()->startOfDay();
                $fechaFin = $temp->copy()->endOfDay();
            }
        } catch (\Exception $e) {
            $fechaInicio = now()->startOfMonth();
            $fechaFin = now();
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

        return [
            'data' => $data,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ];
    }
}

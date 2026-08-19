<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\SeguimientoOrdenCompraReporteData;
use App\Enums\Compras\EstadoOrdenCompra;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ObtenerSeguimientoOrdenesCompraQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): SeguimientoOrdenCompraReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $dataAgrupada = $this->agruparRegistros($this->consultarRegistros($fechaInicio, $fechaFin));

        return new SeguimientoOrdenCompraReporteData(
            data: $dataAgrupada,
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
    private function consultarRegistros(CarbonInterface $fechaInicio, CarbonInterface $fechaFin): Collection
    {
        return DB::table('ordenes_compra as oc')
            ->leftJoin('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->leftJoin('solicitudes_compra as sc', 'oc.solicitud_id', '=', 'sc.id')
            ->leftJoin('recepciones_compra as rc', 'oc.id', '=', 'rc.orden_compra_id')
            ->select(
                'oc.id as orden_id',
                'oc.codigo',
                'oc.fecha_orden',
                'oc.estado',
                'oc.total as monto_total',
                DB::raw("coalesce(obtener_nombre_completo(prov.persona_id::int), 'Sin Proveedor') as proveedor_nombre"),
                'sc.codigo as solicitud_codigo',
                'rc.fecha_recepcion'
            )
            ->whereNull('oc.deleted_at')
            ->whereBetween('oc.fecha_orden', [$fechaInicio, $fechaFin])
            ->orderBy('oc.fecha_orden')
            ->get();
    }

    /**
     * @param  Collection<int, stdClass>  $data
     * @return array<int, mixed>
     */
    private function agruparRegistros(Collection $data): array
    {
        return $data->groupBy('orden_id')->map(function ($items) {
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
            $estadoLabel = is_int($first->estado) || is_string($first->estado)
                ? EstadoOrdenCompra::etiquetaPara($first->estado)
                : '—';

            return (object) [
                'codigo' => $first->codigo,
                'fecha_orden' => $first->fecha_orden,
                'fecha_entrega_estimada' => null,
                'departamento' => '—',
                'estado' => $estadoLabel,
                'estado_label' => $estadoLabel,
                'total' => (float) $first->monto_total,
                'monto_total' => (float) $first->monto_total,
                'proveedor' => $first->proveedor_nombre,
                'proveedor_nombre' => $first->proveedor_nombre,
                'solicitud' => $first->solicitud_codigo,
                'solicitud_codigo' => $first->solicitud_codigo,
                'total_recepciones' => $recepcionCount,
                'recepcion_count' => $recepcionCount,
            ];
        })->values()->toArray();
    }
}

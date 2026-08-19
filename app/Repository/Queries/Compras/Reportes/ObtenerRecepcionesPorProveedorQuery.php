<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\RecepcionesPorProveedorReporteData;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ObtenerRecepcionesPorProveedorQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr): RecepcionesPorProveedorReporteData
    {
        [$fechaInicio, $fechaFin] = $this->resolverRangoFechas($fechaInicioStr, $fechaFinStr);
        $grouped = $this->agruparRegistros($this->consultarRegistros($fechaInicio, $fechaFin));

        return new RecepcionesPorProveedorReporteData(
            data: $grouped,
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
        return DB::table('recepciones_compra as rc')
            ->join('ordenes_compra as oc', 'rc.orden_compra_id', '=', 'oc.id')
            ->join('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->leftJoin('recepcion_items as ri', 'rc.id', '=', 'ri.recepcion_id')
            ->leftJoin('orden_compra_items as oci', 'ri.orden_item_id', '=', 'oci.id')
            ->select(
                'prov.id as proveedor_id',
                DB::raw("coalesce(obtener_nombre_completo(prov.persona_id::int), 'Sin Proveedor') as proveedor_nombre"),
                'rc.id as recepcion_id',
                'rc.fecha_recepcion',
                'oc.total as orden_total',
                DB::raw('coalesce(ri.cantidad_recibida, 0) as cantidad_recibida'),
                DB::raw('coalesce(ri.cantidad_rechazada, 0) as cantidad_rechazada'),
                DB::raw('coalesce(ri.cantidad_recibida * oci.precio_unitario, 0) as monto_linea')
            )
            ->whereNull('rc.deleted_at')
            ->whereNull('oc.deleted_at')
            ->whereBetween('rc.fecha_recepcion', [$fechaInicio, $fechaFin])
            ->get();
    }

    /**
     * @param  Collection<int, stdClass>  $data
     * @return array<int, mixed>
     */
    private function agruparRegistros(Collection $data): array
    {
        return $data->groupBy('proveedor_id')->map(function ($items) {
            $first = $items->first();
            $totalRecepciones = $items->pluck('recepcion_id')->unique()->count();
            $totalCantRecibida = (float) $items->sum(fn (stdClass $row) => (float) $row->cantidad_recibida);
            $totalCantRechazada = (float) $items->sum(fn (stdClass $row) => (float) $row->cantidad_rechazada);
            $montoRecibido = (float) $items->sum(fn (stdClass $row) => (float) $row->monto_linea);

            if ($montoRecibido <= 0 && $items->isNotEmpty()) {
                $montoRecibido = (float) $items->unique('recepcion_id')->sum(fn (stdClass $row) => (float) $row->orden_total);
            }

            return (object) [
                'proveedor_nombre' => $first ? (string) ($first->proveedor_nombre ?? '') : '',
                'total_recepciones' => $totalRecepciones,
                'total_cantidad_recibida' => $totalCantRecibida,
                'total_cantidad_rechazada' => $totalCantRechazada,
                'monto_total_recibido' => $montoRecibido,
                'total_monto' => $montoRecibido,
            ];
        })->values()->toArray();
    }
}

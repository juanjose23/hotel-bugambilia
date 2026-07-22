<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Actions\Shared\ParsearFecha;
use App\BusinessLogic\Compras\Data\Reportes\AnalisisPrecioHistoricoReporteData;
use Illuminate\Support\Facades\DB;

final class ObtenerAnalisisPrecioHistoricoQuery
{
    public function ejecutar(?string $fechaInicioStr, ?string $fechaFinStr, int $meses = 6): AnalisisPrecioHistoricoReporteData
    {
        $parsearFecha = app(ParsearFecha::class);
        $fechaFin = $parsearFecha->ejecutar($fechaFinStr, now());
        $fechaInicio = $parsearFecha->ejecutar($fechaInicioStr, $fechaFin->copy()->subMonths($meses)->startOfMonth());

        if ($fechaInicio->gt($fechaFin)) {
            [$fechaInicio, $fechaFin] = [$fechaFin->copy()->startOfDay(), $fechaInicio->copy()->endOfDay()];
        }

        $data = DB::table('orden_compra_items as oci')
            ->leftJoin('ordenes_compra as oc', 'oci.orden_compra_id', '=', 'oc.id')
            ->leftJoin('productos as p', 'oci.producto_id', '=', 'p.id')
            ->leftJoin('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->select(
                'p.id as producto_id',
                'p.nombre as producto_nombre',
                DB::raw('obtener_nombre_completo(prov.persona_id::int) as proveedor_nombre'),
                'oc.fecha_orden',
                'oci.precio_unitario',
                'oci.cantidad',
                'oci.subtotal'
            )
            ->whereNull('oc.deleted_at')
            ->whereDate('oc.fecha_orden', '>=', $fechaInicio->toDateString())
            ->whereDate('oc.fecha_orden', '<=', $fechaFin->toDateString())
            ->orderBy('p.nombre')
            ->orderBy('oc.fecha_orden')
            ->get();

        $grouped = $data->groupBy('producto_id')->map(function ($items) {
            return (object) [
                'producto_nombre' => (string) ($items->first()->producto_nombre ?? ''),
                'entradas' => $items->map(fn ($i) => (object) [
                    'fecha' => $i->fecha_orden,
                    'proveedor' => $i->proveedor_nombre,
                    'precio_unitario' => $i->precio_unitario,
                    'cantidad' => $i->cantidad,
                ])->values()->toArray(),
                'precio_min' => $items->min('precio_unitario'),
                'precio_max' => $items->max('precio_unitario'),
                'precio_promedio' => round((float) $items->avg('precio_unitario'), 2),
            ];
        })->values()->toArray();

        return new AnalisisPrecioHistoricoReporteData(
            data: $grouped,
            fechaInicio: $fechaInicio->format('d/m/Y'),
            fechaFin: $fechaFin->format('d/m/Y'),
            meses: $meses,
        );
    }
}

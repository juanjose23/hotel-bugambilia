<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\BusinessLogic\Compras\Data\Reportes\TrazabilidadCompletaReporteData;
use App\Repository\Models\Compras\Solicitud;
use Illuminate\Support\Facades\DB;

final class ObtenerTrazabilidadCompletaQuery
{
    public function ejecutar(Solicitud $solicitud): TrazabilidadCompletaReporteData
    {

        $solicitudData = DB::table('solicitudes_compra as sc')
            ->leftJoin('catalogos as dep', 'sc.departamento_solicitante_id', '=', 'dep.id')
            ->leftJoin('colaboradores as col', 'sc.colaborador_id', '=', 'col.id')
            ->select(
                'sc.id',
                'sc.codigo',
                'sc.estado',
                'sc.fecha_solicitud',
                'sc.motivo',
                'dep.nombre as departamento',
                DB::raw("coalesce(obtener_nombre_completo(col.persona_id), 'N/A') as solicitante")
            )
            ->where('sc.id', $solicitud->id)
            ->first();

        $items = DB::table('solicitud_items as si')
            ->leftJoin('productos as p', 'si.producto_id', '=', 'p.id')
            ->leftJoin('producto_variantes as pv', 'si.producto_variante_id', '=', 'pv.id')
            ->select('p.nombre as producto', 'pv.codigo as variante', 'si.cantidad_solicitada', 'si.cantidad_aprobada')
            ->where('si.solicitud_id', $solicitud->id)
            ->get()->toArray();

        if ($solicitudData !== null) {
            $solicitudData->items = $items;
        }

        $cotizaciones = DB::table('cotizaciones as cot')
            ->leftJoin('proveedores as prov', 'cot.proveedor_id', '=', 'prov.id')
            ->select(
                'cot.id',
                DB::raw("coalesce(obtener_nombre_completo(prov.persona_id), 'Sin Proveedor') as proveedor_nombre"),
                'cot.total',
                'cot.tiempo_entrega_dias',
                'cot.es_elegida'
            )
            ->where('cot.solicitud_id', $solicitud->id)
            ->whereNull('cot.deleted_at')
            ->get()->toArray();

        $ordenes = DB::table('ordenes_compra as oc')
            ->leftJoin('proveedores as prov', 'oc.proveedor_id', '=', 'prov.id')
            ->leftJoin('catalogos as cp', 'oc.condicion_pago_id', '=', 'cp.id')
            ->select(
                'oc.id',
                'oc.codigo', DB::raw("
                    CASE oc.estado
                        WHEN 1 THEN 'Borrador'
                        WHEN 2 THEN 'Emitida'
                        WHEN 3 THEN 'En Tránsito'
                        WHEN 4 THEN 'Recibida'
                        WHEN 5 THEN 'Cancelada'
                        WHEN 6 THEN 'Parcial'
                        WHEN 7 THEN 'Vencida'
                        WHEN 8 THEN 'Rechazada'
                        ELSE 'Desconocido'
                    END AS estado_nombre
                "),
                'oc.fecha_orden',
                'oc.total',
                'cp.nombre as condicion_pago',
                DB::raw('obtener_nombre_completo(prov.persona_id) as proveedor_nombre')
            )
            ->where('oc.solicitud_id', $solicitud->id)
            ->whereNull('oc.deleted_at')
            ->get();

        $ordenesConRecepciones = $ordenes->map(function ($orden) {
            $recepciones = DB::table('recepciones_compra as rc')
                ->leftJoin('users as u', 'rc.recibido_por_id', '=', 'u.id')
                ->select(
                    'rc.codigo',
                    DB::raw("
        CASE rc.estado
            WHEN 0 THEN 'Pendiente'
            WHEN 1 THEN 'Completa'
            WHEN 2 THEN 'Parcial'
            WHEN 3 THEN 'Con Discrepancia'
            WHEN 4 THEN 'En Cuarentena'
            WHEN 5 THEN 'Rechazada'
            ELSE 'Desconocido'
        END AS estado_nombre
    "),
                    'rc.fecha_recepcion',
                    'rc.factura_referencia',
                    DB::raw('u.name as receptor')
                )
                ->where('rc.orden_compra_id', $orden->id)
                ->whereNull('rc.deleted_at')
                ->get()->toArray();

            $orden->recepciones = $recepciones;

            return $orden;
        })->toArray();

        $recepciones = DB::table('recepciones_compra as rc')
            ->join('ordenes_compra as oc', 'rc.orden_compra_id', '=', 'oc.id')
            ->select(
                'rc.codigo',
                DB::raw("
        CASE rc.estado
            WHEN 0 THEN 'Pendiente'
            WHEN 1 THEN 'Completa'
            WHEN 2 THEN 'Parcial'
            WHEN 3 THEN 'Con Discrepancia'
            WHEN 4 THEN 'En Cuarentena'
            WHEN 5 THEN 'Rechazada'
            ELSE 'Desconocido'
        END AS estado_nombre
    "),
                'rc.fecha_recepcion',
                'rc.factura_referencia',
                'oc.codigo as orden_codigo'
            )
            ->where('oc.solicitud_id', $solicitud->id)
            ->whereNull('rc.deleted_at')
            ->whereNull('oc.deleted_at')
            ->get()->toArray();

        return new TrazabilidadCompletaReporteData(
            solicitud: $solicitudData ?? (object) ['codigo' => $solicitud->codigo],
            cotizaciones: $cotizaciones,
            ordenesCompra: $ordenesConRecepciones,
            recepciones: $recepciones,
        );
    }
}

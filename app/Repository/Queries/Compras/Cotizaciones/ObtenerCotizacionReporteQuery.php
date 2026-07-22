<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionItemReporteData;
use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionReporteData;
use App\BusinessLogic\Compras\Data\Shared\MonedaReporteData;
use App\BusinessLogic\Compras\Data\Shared\PersonaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use App\Repository\Models\Compras\Cotizacion;

final class ObtenerCotizacionReporteQuery
{
    public function ejecutar(int $id): ?CotizacionReporteData
    {
        $cotizacion = Cotizacion::with([
            'proveedor.persona.personaJuridica',
            'proveedor.persona.personaNatural',
            'items.producto',
            'items.variante',
            'moneda',
            'solicitud',
        ])->find($id);

        if ($cotizacion === null) {
            return null;
        }

        $provPersona = null;
        if ($cotizacion->proveedor?->persona) {
            $razStr = $cotizacion->proveedor->persona->personaJuridica->razon_social ?? $cotizacion->proveedor->persona->nombre_completo;
            $provPersona = new PersonaReporteData(
                primer_nombre: $cotizacion->proveedor->persona->primer_nombre,
                primer_apellido: $cotizacion->proveedor->persona->personaNatural?->primer_apellido,
                nombre_completo: $cotizacion->proveedor->persona->nombre_completo,
                razon_social: $razStr
            );
        }
        $proveedor = new ProveedorReporteData(
            persona: $provPersona,
            contacto_nombre: $cotizacion->proveedor?->contactoPrincipal?->nombre
        );

        $moneda = $cotizacion->moneda ? new MonedaReporteData(codigo: $cotizacion->moneda->codigo, simbolo: $cotizacion->moneda->simbolo) : null;

        $solicitud = null;
        if ($cotizacion->solicitud) {
            $solicitud = new SolicitudReporteData(
                id: $cotizacion->solicitud->id,
                codigo: $cotizacion->solicitud->codigo,
                fecha_solicitud: $cotizacion->solicitud->fecha_solicitud,
                fecha_necesita: $cotizacion->solicitud->fecha_necesita,
                motivo: $cotizacion->solicitud->motivo,
                notas: $cotizacion->solicitud->notas,
                colaborador: null,
                departamentoSolicitante: null,
                estado: null,
                items: collect(),
                cotizaciones: collect()
            );
        }

        $items = collect();
        foreach ($cotizacion->items as $item) {
            $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
            $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
            $items->push(new CotizacionItemReporteData(
                id: $item->id,
                producto_id: (int) $item->producto_id,
                producto: $producto,
                variante: $variante,
                cantidad: (float) $item->cantidad,
                precio_unitario: (float) $item->precio_unitario,
                subtotal: (float) $item->subtotal,
                es_elegido: (bool) $item->es_elegido
            ));
        }

        return new CotizacionReporteData(
            id: $cotizacion->id,
            solicitud_id: (int) $cotizacion->solicitud_id,
            proveedor_id: (int) $cotizacion->proveedor_id,
            proveedor: $proveedor,
            moneda: $moneda,
            total: (float) $cotizacion->total,
            tiempo_entrega_dias: (int) $cotizacion->dias_entrega,
            dias_entrega: (int) $cotizacion->dias_entrega,
            fecha_cotizacion: $cotizacion->fecha_cotizacion,
            es_elegida: (bool) $cotizacion->es_elegida,
            tasa_cambio: (float) ($cotizacion->tasa_cambio ?: 1.0),
            observaciones: $cotizacion->observaciones,
            solicitud: $solicitud,
            items: $items
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\OrdenesCompra;

use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionReporteData;
use App\BusinessLogic\Compras\Data\OrdenesCompra\OrdenCompraItemReporteData;
use App\BusinessLogic\Compras\Data\OrdenesCompra\OrdenCompraReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use App\BusinessLogic\Compras\Data\Shared\MonedaReporteData;
use App\BusinessLogic\Compras\Data\Shared\PersonaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Shared\ValorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use App\Repository\Models\Compras\OrdenCompra;

final class ObtenerOrdenCompraReporteQuery
{
    public function ejecutar(int $id): ?OrdenCompraReporteData
    {
        $orden = OrdenCompra::with([
            'proveedor.persona',
            'proveedor.contactoPrincipal',
            'items.producto',
            'items.variante',
            'items.unidadMedida',
            'solicitud',
            'cotizacion.moneda',
        ])->find($id);

        if ($orden === null) {
            return null;
        }

        $provPersona = null;
        if ($orden->proveedor?->persona) {
            $razStr = $orden->proveedor->persona->personaJuridica->razon_social ?? $orden->proveedor->persona->nombre_completo;
            $provPersona = new PersonaReporteData(
                primer_nombre: $orden->proveedor->persona->primer_nombre,
                primer_apellido: $orden->proveedor->persona->personaNatural?->primer_apellido,
                nombre_completo: $orden->proveedor->persona->nombre_completo,
                razon_social: $razStr
            );
        }
        $proveedor = new ProveedorReporteData(
            persona: $provPersona,
            contacto_nombre: $orden->proveedor?->contactoPrincipal?->nombre
        );

        $condPago = $orden->condicionPago ? new ValorReporteData(valor: $orden->condicionPago->nombre) : null;

        $solicitud = null;
        if ($orden->solicitud) {
            $solicitud = new SolicitudReporteData(
                id: $orden->solicitud->id,
                codigo: $orden->solicitud->codigo,
                fecha_solicitud: $orden->solicitud->fecha_solicitud,
                fecha_necesita: $orden->solicitud->fecha_necesita,
                motivo: $orden->solicitud->motivo,
                notas: $orden->solicitud->notas,
                colaborador: null,
                departamentoSolicitante: null,
                estado: null,
                items: collect(),
                cotizaciones: collect()
            );
        }

        $cotizacion = null;
        if ($orden->cotizacion) {
            $moneda = $orden->cotizacion->moneda ? new MonedaReporteData(codigo: $orden->cotizacion->moneda->codigo, simbolo: $orden->cotizacion->moneda->simbolo) : null;
            $cotizacion = new CotizacionReporteData(
                id: $orden->cotizacion->id,
                solicitud_id: (int) $orden->cotizacion->solicitud_id,
                proveedor_id: (int) $orden->cotizacion->proveedor_id,
                proveedor: null,
                moneda: $moneda,
                total: (float) $orden->cotizacion->total,
                tiempo_entrega_dias: (int) $orden->cotizacion->dias_entrega,
                dias_entrega: (int) $orden->cotizacion->dias_entrega,
                fecha_cotizacion: $orden->cotizacion->fecha_cotizacion,
                es_elegida: (bool) $orden->cotizacion->es_elegida,
                tasa_cambio: (float) ($orden->cotizacion->tasa_cambio ?: 1.0),
                observaciones: $orden->cotizacion->observaciones,
                solicitud: null,
                items: collect()
            );
        }

        $estado = new EstadoReporteData(
            value: $orden->estado->value,
            label: $orden->estado->label()
        );

        $items = collect();
        foreach ($orden->items as $item) {
            $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
            $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
            $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;

            $items->push(new OrdenCompraItemReporteData(
                id: $item->id,
                producto: $producto,
                variante: $variante,
                unidadMedida: $uMedida,
                cantidad: (float) $item->cantidad,
                precio_unitario: (float) $item->precio_unitario,
                subtotal: (float) $item->subtotal
            ));
        }

        return new OrdenCompraReporteData(
            id: $orden->id,
            codigo: $orden->codigo,
            fecha_orden: $orden->fecha_orden,
            fecha_entrega_estimada: $orden->fecha_entrega_estimada,
            total: (float) $orden->total,
            subtotal: (float) $orden->subtotal,
            impuestos: (float) $orden->impuestos,
            tasa_cambio: (float) ($orden->tasa_cambio ?: 1.0),
            proveedor: $proveedor,
            condicionPago: $condPago,
            solicitud: $solicitud,
            cotizacion: $cotizacion,
            estado: $estado,
            items: $items
        );
    }
}

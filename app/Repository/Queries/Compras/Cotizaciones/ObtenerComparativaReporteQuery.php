<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Cotizaciones;

use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionItemReporteData;
use App\BusinessLogic\Compras\Data\Cotizaciones\CotizacionReporteData;
use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\DepartamentoReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use App\BusinessLogic\Compras\Data\Shared\MonedaReporteData;
use App\BusinessLogic\Compras\Data\Shared\PersonaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudItemReporteData;
use App\BusinessLogic\Compras\Data\Solicitudes\SolicitudReporteData;
use App\Repository\Models\Compras\Solicitud;

final class ObtenerComparativaReporteQuery
{
    public function ejecutar(int $id): ?SolicitudReporteData
    {
        $solicitud = Solicitud::with([
            'colaborador.persona',
            'departamentoSolicitante',
            'items.producto',
            'items.variante',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.proveedor.persona.personaNatural',
            'cotizaciones.items.producto',
            'cotizaciones.items.variante',
            'cotizaciones.moneda',
        ])->find($id);

        if ($solicitud === null) {
            return null;
        }

        $colaborador = null;
        if ($solicitud->colaborador) {
            $persona = null;
            if ($solicitud->colaborador->persona) {
                $persona = new PersonaReporteData(
                    primer_nombre: $solicitud->colaborador->persona->primer_nombre,
                    primer_apellido: $solicitud->colaborador->persona->personaNatural?->primer_apellido,
                    nombre_completo: $solicitud->colaborador->persona->nombre_completo,
                    razon_social: null,
                );
            }
            $colaborador = new ColaboradorReporteData(
                codigo: $solicitud->colaborador->codigo,
                persona: $persona
            );
        }

        $dept = null;
        if ($solicitud->departamentoSolicitante) {
            $dept = new DepartamentoReporteData(nombre: $solicitud->departamentoSolicitante->nombre);
        }

        $estado = new EstadoReporteData(
            value: $solicitud->estado->value,
            label: $solicitud->estado->label()
        );

        $items = collect();
        foreach ($solicitud->items as $item) {
            $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
            $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
            $prodVariante = $item->productoVariante ? new VarianteReporteData(codigo: $item->productoVariante->codigo, nombre_variante: $item->productoVariante->nombre_variante) : null;
            $items->push(new SolicitudItemReporteData(
                id: $item->id,
                producto_id: (int) $item->producto_id,
                producto: $producto,
                productoVariante: $prodVariante,
                variante: $variante,
                cantidad_solicitada: (float) $item->cantidad_solicitada,
                cantidad_aprobada: (float) $item->cantidad_aprobada
            ));
        }

        $cotizaciones = collect();
        foreach ($solicitud->cotizaciones as $cot) {
            $provPersona = null;
            if ($cot->proveedor?->persona) {
                $razStr = $cot->proveedor->persona->personaJuridica->razon_social ?? $cot->proveedor->persona->nombre_completo;
                $provPersona = new PersonaReporteData(
                    primer_nombre: $cot->proveedor->persona->primer_nombre,
                    primer_apellido: $cot->proveedor->persona->personaNatural?->primer_apellido,
                    nombre_completo: $cot->proveedor->persona->nombre_completo,
                    razon_social: $razStr
                );
            }
            $proveedor = new ProveedorReporteData(
                persona: $provPersona,
                contacto_nombre: $cot->proveedor?->contactoPrincipal?->nombre
            );
            $moneda = $cot->moneda ? new MonedaReporteData(codigo: $cot->moneda->codigo, simbolo: $cot->moneda->simbolo) : null;

            $cotItems = collect();
            foreach ($cot->items as $cItem) {
                $cProd = $cItem->producto ? new ProductoReporteData(nombre: $cItem->producto->nombre) : null;
                $cVar = $cItem->variante ? new VarianteReporteData(codigo: $cItem->variante->codigo, nombre_variante: $cItem->variante->nombre_variante) : null;
                $cotItems->push(new CotizacionItemReporteData(
                    id: $cItem->id,
                    producto_id: $cItem->producto_id,
                    producto: $cProd,
                    variante: $cVar,
                    cantidad: (float) $cItem->cantidad,
                    precio_unitario: (float) $cItem->precio_unitario,
                    subtotal: (float) $cItem->subtotal,
                    es_elegido: $cItem->es_elegido
                ));
            }

            $cotizaciones->push(new CotizacionReporteData(
                id: $cot->id,
                solicitud_id: (int) $cot->solicitud_id,
                proveedor_id: (int) $cot->proveedor_id,
                proveedor: $proveedor,
                moneda: $moneda,
                total: (float) $cot->total,
                tiempo_entrega_dias: (int) $cot->dias_entrega,
                dias_entrega: (int) $cot->dias_entrega,
                fecha_cotizacion: $cot->fecha_cotizacion,
                es_elegida: (bool) $cot->es_elegida,
                tasa_cambio: (float) ($cot->tasa_cambio ?: 1.0),
                observaciones: $cot->observaciones,
                solicitud: null,
                items: $cotItems
            ));
        }

        return new SolicitudReporteData(
            id: $solicitud->id,
            codigo: $solicitud->codigo,
            fecha_solicitud: $solicitud->fecha_solicitud,
            fecha_necesita: $solicitud->fecha_necesita,
            motivo: $solicitud->motivo,
            notas: $solicitud->notas,
            colaborador: $colaborador,
            departamentoSolicitante: $dept,
            estado: $estado,
            items: $items,
            cotizaciones: $cotizaciones
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Recepciones;

use App\BusinessLogic\Compras\Data\OrdenesCompra\OrdenCompraReporteData;
use App\BusinessLogic\Compras\Data\Recepciones\RecepcionItemReporteData;
use App\BusinessLogic\Compras\Data\Recepciones\RecepcionReporteData;
use App\BusinessLogic\Compras\Data\Shared\ColaboradorReporteData;
use App\BusinessLogic\Compras\Data\Shared\EstadoReporteData;
use App\BusinessLogic\Compras\Data\Shared\PersonaReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProductoReporteData;
use App\BusinessLogic\Compras\Data\Shared\ProveedorReporteData;
use App\BusinessLogic\Compras\Data\Shared\ValorReporteData;
use App\BusinessLogic\Compras\Data\Shared\VarianteReporteData;
use App\Repository\Models\Compras\RecepcionCompra;

final class ObtenerRecepcionReporteQuery
{
    public function ejecutar(int $id): ?RecepcionReporteData
    {
        $recepcion = RecepcionCompra::with([
            'ordenCompra.proveedor.persona.personaJuridica',
            'ordenCompra.proveedor.persona.personaNatural',
            'ordenCompra.proveedor.contactoPrincipal',
            'ordenCompra.cotizacion.moneda',
            'receptor.persona',
            'items.producto',
            'items.variante',
            'items.unidadMedida',
        ])->find($id);

        if ($recepcion === null) {
            return null;
        }

        $receptor = null;
        if ($recepcion->receptor) {
            $recPersona = null;
            if ($recepcion->receptor->persona) {
                $recPersona = new PersonaReporteData(
                    primer_nombre: $recepcion->receptor->persona->primer_nombre,
                    primer_apellido: $recepcion->receptor->persona->personaNatural?->primer_apellido,
                    nombre_completo: $recepcion->receptor->persona->nombre_completo,
                    razon_social: null
                );
            }
            $receptor = new ColaboradorReporteData(
                codigo: (string) $recepcion->receptor->id,
                persona: $recPersona
            );
        }

        $ordenCompra = null;
        if ($recepcion->ordenCompra) {
            $provPersona = null;
            if ($recepcion->ordenCompra->proveedor?->persona) {
                $razStr = $recepcion->ordenCompra->proveedor->persona->personaJuridica->razon_social ?? $recepcion->ordenCompra->proveedor->persona->nombre_completo;
                $provPersona = new PersonaReporteData(
                    primer_nombre: $recepcion->ordenCompra->proveedor->persona->primer_nombre,
                    primer_apellido: $recepcion->ordenCompra->proveedor->persona->personaNatural?->primer_apellido,
                    nombre_completo: $recepcion->ordenCompra->proveedor->persona->nombre_completo,
                    razon_social: $razStr
                );
            }
            $proveedor = new ProveedorReporteData(
                persona: $provPersona,
                contacto_nombre: $recepcion->ordenCompra->proveedor?->contactoPrincipal?->nombre
            );
            $ordenCompra = new OrdenCompraReporteData(
                id: $recepcion->ordenCompra->id,
                codigo: $recepcion->ordenCompra->codigo,
                fecha_orden: $recepcion->ordenCompra->fecha_orden,
                fecha_entrega_estimada: $recepcion->ordenCompra->fecha_entrega_estimada,
                total: (float) $recepcion->ordenCompra->total,
                subtotal: (float) $recepcion->ordenCompra->subtotal,
                impuestos: (float) $recepcion->ordenCompra->impuestos,
                tasa_cambio: (float) ($recepcion->ordenCompra->tasa_cambio ?: 1.0),
                proveedor: $proveedor,
                condicionPago: null,
                solicitud: null,
                cotizacion: null,
                estado: null,
                items: collect()
            );
        }

        $estado = new EstadoReporteData(
            value: (string) $recepcion->estado->value,
            label: $recepcion->estado->label()
        );

        $items = collect();
        foreach ($recepcion->items as $item) {
            $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
            $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
            $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;
            $items->push(new RecepcionItemReporteData(
                id: $item->id,
                producto: $producto,
                variante: $variante,
                unidadMedida: $uMedida,
                cantidad_recibida: (float) $item->cantidad_recibida,
                cantidad_rechazada: (float) $item->cantidad_rechazada,
                observaciones: $item->observaciones
            ));
        }

        return new RecepcionReporteData(
            id: $recepcion->id,
            codigo: $recepcion->codigo,
            fecha_recepcion: $recepcion->fecha_recepcion,
            guia_remision: $recepcion->guia_remision,
            factura_referencia: $recepcion->factura_referencia,
            receptor: $receptor,
            ordenCompra: $ordenCompra,
            estado: $estado,
            items: $items
        );
    }
}

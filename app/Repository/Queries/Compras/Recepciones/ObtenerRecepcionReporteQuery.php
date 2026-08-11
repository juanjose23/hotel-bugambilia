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
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\RecepcionItem;
use App\Repository\Models\User;

final class ObtenerRecepcionReporteQuery
{
    public function ejecutar(int $id): ?RecepcionReporteData
    {
        $recepcion = RecepcionCompra::with([
            'ordenCompra.proveedor.persona.personaJuridica',
            'ordenCompra.proveedor.persona.personaNatural',
            'ordenCompra.proveedor.contactoPrincipal',
            'ordenCompra.cotizacion.moneda',
            'receptor.persona.personaNatural',
            'items.producto',
            'items.variante',
            'items.unidadMedida',
        ])->find($id);

        if ($recepcion === null) {
            return null;
        }

        $estado = new EstadoReporteData(
            value: (string) $recepcion->estado->value,
            label: $recepcion->estado->label()
        );

        return new RecepcionReporteData(
            id: $recepcion->id,
            codigo: $recepcion->codigo,
            fecha_recepcion: $recepcion->fecha_recepcion,
            guia_remision: $recepcion->guia_remision,
            factura_referencia: $recepcion->factura_referencia,
            receptor: $this->mapearReceptor($recepcion->receptor),
            ordenCompra: $this->mapearOrdenCompra($recepcion->ordenCompra),
            estado: $estado,
            items: $recepcion->items->map(fn ($item) => $this->mapearItem($item))
        );
    }

    private function mapearReceptor(?User $receptor): ?ColaboradorReporteData
    {
        if (! $receptor) {
            return null;
        }

        $recPersona = null;
        if ($receptor->persona) {
            $recPersona = new PersonaReporteData(
                primer_nombre: $receptor->persona->primer_nombre,
                primer_apellido: $receptor->persona->personaNatural?->primer_apellido,
                nombre_completo: $receptor->persona->nombre_completo,
                razon_social: null
            );
        }

        return new ColaboradorReporteData(
            codigo: (string) $receptor->id,
            persona: $recPersona
        );
    }

    private function mapearOrdenCompra(?OrdenCompra $ordenCompra): ?OrdenCompraReporteData
    {
        if (! $ordenCompra) {
            return null;
        }

        return new OrdenCompraReporteData(
            id: $ordenCompra->id,
            codigo: $ordenCompra->codigo,
            fecha_orden: $ordenCompra->fecha_orden,
            fecha_entrega_estimada: $ordenCompra->fecha_entrega_estimada,
            total: (float) $ordenCompra->total,
            subtotal: (float) $ordenCompra->subtotal,
            impuestos: (float) $ordenCompra->impuestos,
            tasa_cambio: (float) ($ordenCompra->tasa_cambio ?: 1.0),
            proveedor: $this->mapearProveedor($ordenCompra->proveedor),
            condicionPago: null,
            solicitud: null,
            cotizacion: null,
            estado: null,
            items: collect()
        );
    }

    private function mapearProveedor(?Proveedor $proveedor): ProveedorReporteData
    {
        $provPersona = null;
        if ($proveedor?->persona) {
            $razStr = $proveedor->persona->personaJuridica->razon_social ?? $proveedor->persona->nombre_completo;
            $provPersona = new PersonaReporteData(
                primer_nombre: $proveedor->persona->primer_nombre,
                primer_apellido: $proveedor->persona->personaNatural?->primer_apellido,
                nombre_completo: $proveedor->persona->nombre_completo,
                razon_social: $razStr
            );
        }

        return new ProveedorReporteData(
            persona: $provPersona,
            contacto_nombre: $proveedor?->contactoPrincipal?->nombre
        );
    }

    private function mapearItem(RecepcionItem $item): RecepcionItemReporteData
    {
        $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
        $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
        $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;

        return new RecepcionItemReporteData(
            id: $item->id,
            producto: $producto,
            variante: $variante,
            unidadMedida: $uMedida,
            cantidad_recibida: (float) $item->cantidad_recibida,
            cantidad_rechazada: (float) $item->cantidad_rechazada,
            observaciones: $item->observaciones
        );
    }
}

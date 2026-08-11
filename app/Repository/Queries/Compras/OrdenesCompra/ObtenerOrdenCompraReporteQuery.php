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
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\OrdenCompraItem;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\Solicitud;

final class ObtenerOrdenCompraReporteQuery
{
    public function ejecutar(int $id): ?OrdenCompraReporteData
    {
        $orden = OrdenCompra::with([
            'condicionPago',
            'proveedor.persona.personaJuridica',
            'proveedor.persona.personaNatural',
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

        $condPago = $orden->condicionPago ? new ValorReporteData(valor: $orden->condicionPago->nombre) : null;

        $estado = new EstadoReporteData(
            value: $orden->estado->value,
            label: $orden->estado->label()
        );

        return new OrdenCompraReporteData(
            id: $orden->id,
            codigo: $orden->codigo,
            fecha_orden: $orden->fecha_orden,
            fecha_entrega_estimada: $orden->fecha_entrega_estimada,
            total: (float) $orden->total,
            subtotal: (float) $orden->subtotal,
            impuestos: (float) $orden->impuestos,
            tasa_cambio: (float) ($orden->tasa_cambio ?: 1.0),
            proveedor: $this->mapearProveedor($orden->proveedor),
            condicionPago: $condPago,
            solicitud: $this->mapearSolicitud($orden->solicitud),
            cotizacion: $this->mapearCotizacion($orden->cotizacion),
            estado: $estado,
            items: $orden->items->map(fn ($item) => $this->mapearItem($item))
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

    private function mapearSolicitud(?Solicitud $solicitud): ?SolicitudReporteData
    {
        if (! $solicitud) {
            return null;
        }

        return new SolicitudReporteData(
            id: $solicitud->id,
            codigo: $solicitud->codigo,
            fecha_solicitud: $solicitud->fecha_solicitud,
            fecha_necesita: $solicitud->fecha_necesita,
            motivo: $solicitud->motivo,
            notas: $solicitud->notas,
            colaborador: null,
            departamentoSolicitante: null,
            estado: null,
            items: collect(),
            cotizaciones: collect()
        );
    }

    private function mapearCotizacion(?Cotizacion $cotizacion): ?CotizacionReporteData
    {
        if (! $cotizacion) {
            return null;
        }

        $moneda = $cotizacion->moneda ? new MonedaReporteData(codigo: $cotizacion->moneda->codigo, simbolo: $cotizacion->moneda->simbolo) : null;

        return new CotizacionReporteData(
            id: $cotizacion->id,
            solicitud_id: (int) $cotizacion->solicitud_id,
            proveedor_id: (int) $cotizacion->proveedor_id,
            proveedor: null,
            moneda: $moneda,
            total: (float) $cotizacion->total,
            tiempo_entrega_dias: (int) $cotizacion->dias_entrega,
            dias_entrega: (int) $cotizacion->dias_entrega,
            fecha_cotizacion: $cotizacion->fecha_cotizacion,
            es_elegida: (bool) $cotizacion->es_elegida,
            tasa_cambio: (float) ($cotizacion->tasa_cambio ?: 1.0),
            observaciones: $cotizacion->observaciones,
            solicitud: null,
            items: collect()
        );
    }

    private function mapearItem(OrdenCompraItem $item): OrdenCompraItemReporteData
    {
        $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
        $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;
        $uMedida = $item->unidadMedida ? new ValorReporteData(valor: $item->unidadMedida->nombre) : null;

        return new OrdenCompraItemReporteData(
            id: $item->id,
            producto: $producto,
            variante: $variante,
            unidadMedida: $uMedida,
            cantidad: (float) $item->cantidad,
            precio_unitario: (float) $item->precio_unitario,
            subtotal: (float) $item->subtotal
        );
    }
}

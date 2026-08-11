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
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Compras\SolicitudItem;

final class ObtenerComparativaReporteQuery
{
    public function ejecutar(int $id): ?SolicitudReporteData
    {
        $solicitud = Solicitud::with([
            'colaborador.persona.personaNatural',
            'departamentoSolicitante',
            'items.producto',
            'items.variante',
            'cotizaciones.proveedor.persona.personaJuridica',
            'cotizaciones.proveedor.persona.personaNatural',
            'cotizaciones.proveedor.contactoPrincipal',
            'cotizaciones.items.producto',
            'cotizaciones.items.variante',
            'cotizaciones.moneda',
        ])->find($id);

        if ($solicitud === null) {
            return null;
        }

        return new SolicitudReporteData(
            id: $solicitud->id,
            codigo: $solicitud->codigo,
            fecha_solicitud: $solicitud->fecha_solicitud,
            fecha_necesita: $solicitud->fecha_necesita,
            motivo: $solicitud->motivo,
            notas: $solicitud->notas,
            colaborador: $this->mapearColaborador($solicitud->colaborador),
            departamentoSolicitante: $solicitud->departamentoSolicitante ? new DepartamentoReporteData(nombre: $solicitud->departamentoSolicitante->nombre) : null,
            estado: new EstadoReporteData(
                value: $solicitud->estado->value,
                label: $solicitud->estado->label()
            ),
            items: $solicitud->items->map(fn ($item) => $this->mapearSolicitudItem($item)),
            cotizaciones: $solicitud->cotizaciones->map(fn ($cot) => $this->mapearCotizacion($cot))
        );
    }

    private function mapearColaborador(?Colaborador $colaborador): ?ColaboradorReporteData
    {
        if (! $colaborador) {
            return null;
        }

        $persona = null;
        if ($colaborador->persona) {
            $persona = new PersonaReporteData(
                primer_nombre: $colaborador->persona->primer_nombre,
                primer_apellido: $colaborador->persona->personaNatural?->primer_apellido,
                nombre_completo: $colaborador->persona->nombre_completo,
                razon_social: null,
            );
        }

        return new ColaboradorReporteData(
            codigo: $colaborador->codigo,
            persona: $persona
        );
    }

    private function mapearSolicitudItem(SolicitudItem $item): SolicitudItemReporteData
    {
        $producto = $item->producto ? new ProductoReporteData(nombre: $item->producto->nombre) : null;
        $variante = $item->variante ? new VarianteReporteData(codigo: $item->variante->codigo, nombre_variante: $item->variante->nombre_variante) : null;

        return new SolicitudItemReporteData(
            id: $item->id,
            producto_id: (int) $item->producto_id,
            producto: $producto,
            productoVariante: $variante,
            variante: $variante,
            cantidad_solicitada: (float) $item->cantidad_solicitada,
            cantidad_aprobada: (float) $item->cantidad_aprobada
        );
    }

    private function mapearCotizacion(Cotizacion $cot): CotizacionReporteData
    {
        return new CotizacionReporteData(
            id: $cot->id,
            solicitud_id: (int) $cot->solicitud_id,
            proveedor_id: (int) $cot->proveedor_id,
            proveedor: $this->mapearProveedor($cot->proveedor),
            moneda: $cot->moneda ? new MonedaReporteData(codigo: $cot->moneda->codigo, simbolo: $cot->moneda->simbolo) : null,
            total: (float) $cot->total,
            tiempo_entrega_dias: (int) $cot->dias_entrega,
            dias_entrega: (int) $cot->dias_entrega,
            fecha_cotizacion: $cot->fecha_cotizacion,
            es_elegida: (bool) $cot->es_elegida,
            tasa_cambio: (float) ($cot->tasa_cambio ?: 1.0),
            observaciones: $cot->observaciones,
            solicitud: null,
            items: $cot->items->map(fn ($cItem) => $this->mapearCotizacionItem($cItem))
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

    private function mapearCotizacionItem(CotizacionItem $cItem): CotizacionItemReporteData
    {
        $cProd = $cItem->producto ? new ProductoReporteData(nombre: $cItem->producto->nombre) : null;
        $cVar = $cItem->variante ? new VarianteReporteData(codigo: $cItem->variante->codigo, nombre_variante: $cItem->variante->nombre_variante) : null;

        return new CotizacionItemReporteData(
            id: $cItem->id,
            producto_id: $cItem->producto_id,
            producto: $cProd,
            variante: $cVar,
            cantidad: (float) $cItem->cantidad,
            precio_unitario: (float) $cItem->precio_unitario,
            subtotal: (float) $cItem->subtotal,
            es_elegido: $cItem->es_elegido
        );
    }
}

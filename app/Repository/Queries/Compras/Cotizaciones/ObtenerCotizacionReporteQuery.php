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
use App\Repository\Models\Compras\CotizacionItem;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Compras\Solicitud;
use App\Repository\Models\Monedas\Moneda;
use Illuminate\Support\Collection;

final class ObtenerCotizacionReporteQuery
{
    public function ejecutar(int $id): ?CotizacionReporteData
    {
        /** @var Cotizacion|null $cotizacion */
        $cotizacion = Cotizacion::with([
            'proveedor.persona.personaJuridica',
            'proveedor.persona.personaNatural',
            'proveedor.contactoPrincipal',
            'items.producto',
            'items.variante',
            'moneda',
            'solicitud',
        ])->find($id);

        if ($cotizacion === null) {
            return null;
        }

        return new CotizacionReporteData(
            id: (int) $cotizacion->getAttribute('id'),
            solicitud_id: (int) $cotizacion->getAttribute('solicitud_id'),
            proveedor_id: (int) $cotizacion->getAttribute('proveedor_id'),
            proveedor: $this->mapearProveedor($cotizacion->relationLoaded('proveedor') ? $cotizacion->getRelation('proveedor') : null),
            moneda: $this->mapearMoneda($cotizacion->relationLoaded('moneda') ? $cotizacion->getRelation('moneda') : null),
            total: (float) $cotizacion->getAttribute('total'),
            tiempo_entrega_dias: (int) $cotizacion->getAttribute('dias_entrega'),
            dias_entrega: (int) $cotizacion->getAttribute('dias_entrega'),
            fecha_cotizacion: $cotizacion->getAttribute('fecha_cotizacion'),
            es_elegida: (bool) $cotizacion->getAttribute('es_elegida'),
            tasa_cambio: (float) ($cotizacion->getAttribute('tasa_cambio') ?: 1.0),
            observaciones: $cotizacion->getAttribute('observaciones'),
            solicitud: $this->mapearSolicitud($cotizacion->relationLoaded('solicitud') ? $cotizacion->getRelation('solicitud') : null),
            items: $this->mapearItems($cotizacion->relationLoaded('items') ? $cotizacion->getRelation('items') : collect())
        );
    }

    private function mapearProveedor(?Proveedor $proveedor): ?ProveedorReporteData
    {
        if ($proveedor === null) {
            return null;
        }

        $persona = $proveedor->relationLoaded('persona') ? $proveedor->getRelation('persona') : null;
        $provPersona = null;

        if ($persona !== null) {
            $personaJuridica = $persona->relationLoaded('personaJuridica') ? $persona->getRelation('personaJuridica') : null;
            $personaNatural = $persona->relationLoaded('personaNatural') ? $persona->getRelation('personaNatural') : null;

            $razonSocialJuridica = $personaJuridica?->getAttribute('razon_social');
            $nombreCompleto = $persona->getAttribute('nombre_completo');
            $razStr = $razonSocialJuridica ?? $nombreCompleto;

            $provPersona = new PersonaReporteData(
                primer_nombre: $persona->getAttribute('primer_nombre'),
                primer_apellido: $personaNatural?->getAttribute('primer_apellido'),
                nombre_completo: $nombreCompleto,
                razon_social: $razStr
            );
        }

        $contactoPrincipal = $proveedor->relationLoaded('contactoPrincipal') ? $proveedor->getRelation('contactoPrincipal') : null;

        return new ProveedorReporteData(
            persona: $provPersona,
            contacto_nombre: $contactoPrincipal?->getAttribute('nombre')
        );
    }

    private function mapearMoneda(?Moneda $moneda): ?MonedaReporteData
    {
        if ($moneda === null) {
            return null;
        }

        return new MonedaReporteData(
            codigo: (string) $moneda->getAttribute('codigo'),
            simbolo: (string) $moneda->getAttribute('simbolo')
        );
    }

    private function mapearSolicitud(?Solicitud $solicitud): ?SolicitudReporteData
    {
        if ($solicitud === null) {
            return null;
        }

        return new SolicitudReporteData(
            id: (int) $solicitud->getAttribute('id'),
            codigo: (string) $solicitud->getAttribute('codigo'),
            fecha_solicitud: $solicitud->getAttribute('fecha_solicitud'),
            fecha_necesita: $solicitud->getAttribute('fecha_necesita'),
            motivo: $solicitud->getAttribute('motivo'),
            notas: $solicitud->getAttribute('notas'),
            colaborador: null,
            departamentoSolicitante: null,
            estado: null,
            items: collect(),
            cotizaciones: collect()
        );
    }

    /**
     * @param  Collection<int, CotizacionItem>  $itemsCollection
     * @return Collection<int, CotizacionItemReporteData>
     */
    private function mapearItems(Collection $itemsCollection): Collection
    {
        return $itemsCollection->map(function (CotizacionItem $item): CotizacionItemReporteData {
            $producto = $item->relationLoaded('producto') ? $item->getRelation('producto') : null;
            $variante = $item->relationLoaded('variante') ? $item->getRelation('variante') : null;

            $prodData = $producto !== null ? new ProductoReporteData(nombre: (string) $producto->getAttribute('nombre')) : null;
            $varData = $variante !== null ? new VarianteReporteData(
                codigo: (string) $variante->getAttribute('codigo'),
                nombre_variante: (string) $variante->getAttribute('nombre_variante')
            ) : null;

            return new CotizacionItemReporteData(
                id: (int) $item->getAttribute('id'),
                producto_id: (int) $item->getAttribute('producto_id'),
                producto: $prodData,
                variante: $varData,
                cantidad: (float) $item->getAttribute('cantidad'),
                precio_unitario: (float) $item->getAttribute('precio_unitario'),
                subtotal: (float) $item->getAttribute('subtotal'),
                es_elegido: (bool) $item->getAttribute('es_elegido')
            );
        });
    }
}

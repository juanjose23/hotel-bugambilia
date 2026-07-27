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
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
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

        /** @var int $cotId */
        $cotId = $cotizacion->getAttribute('id');
        /** @var int $solId */
        $solId = $cotizacion->getAttribute('solicitud_id');
        /** @var int $provId */
        $provId = $cotizacion->getAttribute('proveedor_id');
        /** @var float $total */
        $total = $cotizacion->getAttribute('total');
        /** @var int $diasEntrega */
        $diasEntrega = $cotizacion->getAttribute('dias_entrega');
        /** @var CarbonInterface|null $fechaCotizacion */
        $fechaCotizacion = $cotizacion->getAttribute('fecha_cotizacion');
        /** @var bool $esElegida */
        $esElegida = $cotizacion->getAttribute('es_elegida');
        /** @var float $tasaCambio */
        $tasaCambio = $cotizacion->getAttribute('tasa_cambio') ?: 1.0;
        /** @var string|null $obsCotizacion */
        $obsCotizacion = $cotizacion->getAttribute('observaciones');

        return new CotizacionReporteData(
            id: $cotId,
            solicitud_id: $solId,
            proveedor_id: $provId,
            proveedor: $this->mapearProveedor($cotizacion->relationLoaded('proveedor') ? ($cotizacion->getRelation('proveedor') instanceof Proveedor ? $cotizacion->getRelation('proveedor') : null) : null),
            moneda: $this->mapearMoneda($cotizacion->relationLoaded('moneda') ? ($cotizacion->getRelation('moneda') instanceof Moneda ? $cotizacion->getRelation('moneda') : null) : null),
            total: $total,
            tiempo_entrega_dias: $diasEntrega,
            dias_entrega: $diasEntrega,
            fecha_cotizacion: $fechaCotizacion,
            es_elegida: $esElegida,
            tasa_cambio: $tasaCambio,
            observaciones: $obsCotizacion,
            solicitud: $this->mapearSolicitud($cotizacion->relationLoaded('solicitud') ? ($cotizacion->getRelation('solicitud') instanceof Solicitud ? $cotizacion->getRelation('solicitud') : null) : null),
            items: $this->mapearItems($cotizacion->relationLoaded('items') ? ($cotizacion->getRelation('items') instanceof Collection ? $cotizacion->getRelation('items') : collect()) : collect())
        );
    }

    private function mapearProveedor(?Proveedor $proveedor): ?ProveedorReporteData
    {
        if ($proveedor === null) {
            return null;
        }

        /** @var Model|null $persona */
        $persona = $proveedor->relationLoaded('persona') ? $proveedor->getRelation('persona') : null;
        $provPersona = null;

        if ($persona !== null) {
            /** @var Model|null $personaJuridica */
            $personaJuridica = $persona->relationLoaded('personaJuridica') ? $persona->getRelation('personaJuridica') : null;
            /** @var Model|null $personaNatural */
            $personaNatural = $persona->relationLoaded('personaNatural') ? $persona->getRelation('personaNatural') : null;

            /** @var string|null $razonSocialJuridica */
            $razonSocialJuridica = $personaJuridica?->getAttribute('razon_social');
            /** @var string $nombreCompleto */
            $nombreCompleto = $persona->getAttribute('nombre_completo');
            $razStr = $razonSocialJuridica ?? $nombreCompleto;

            /** @var string $primerNombre */
            $primerNombre = $persona->getAttribute('primer_nombre');
            /** @var string|null $primerApellido */
            $primerApellido = $personaNatural?->getAttribute('primer_apellido');

            $provPersona = new PersonaReporteData(
                primer_nombre: $primerNombre,
                primer_apellido: $primerApellido,
                nombre_completo: $nombreCompleto,
                razon_social: $razStr
            );
        }

        /** @var Model|null $contactoPrincipal */
        $contactoPrincipal = $proveedor->relationLoaded('contactoPrincipal') ? $proveedor->getRelation('contactoPrincipal') : null;

        /** @var string|null $contactoNombre */
        $contactoNombre = $contactoPrincipal?->getAttribute('nombre');

        return new ProveedorReporteData(
            persona: $provPersona,
            contacto_nombre: $contactoNombre
        );
    }

    private function mapearMoneda(?Moneda $moneda): ?MonedaReporteData
    {
        if ($moneda === null) {
            return null;
        }

        /** @var string $codigo */
        $codigo = $moneda->getAttribute('codigo');
        /** @var string $simbolo */
        $simbolo = $moneda->getAttribute('simbolo');

        return new MonedaReporteData(
            codigo: $codigo,
            simbolo: $simbolo
        );
    }

    private function mapearSolicitud(?Solicitud $solicitud): ?SolicitudReporteData
    {
        if ($solicitud === null) {
            return null;
        }

        /** @var int $solId */
        $solId = $solicitud->getAttribute('id');
        /** @var string $codigo */
        $codigo = $solicitud->getAttribute('codigo');
        /** @var CarbonInterface|null $fechaSolicitud */
        $fechaSolicitud = $solicitud->getAttribute('fecha_solicitud');
        /** @var CarbonInterface|null $fechaNecesita */
        $fechaNecesita = $solicitud->getAttribute('fecha_necesita');
        /** @var string|null $motivo */
        $motivo = $solicitud->getAttribute('motivo');
        /** @var string|null $notas */
        $notas = $solicitud->getAttribute('notas');

        return new SolicitudReporteData(
            id: $solId,
            codigo: $codigo,
            fecha_solicitud: $fechaSolicitud,
            fecha_necesita: $fechaNecesita,
            motivo: $motivo,
            notas: $notas,
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
            /** @var Model|null $producto */
            $producto = $item->relationLoaded('producto') ? $item->getRelation('producto') : null;
            /** @var Model|null $variante */
            $variante = $item->relationLoaded('variante') ? $item->getRelation('variante') : null;

            /** @var string $prodNombre */
            $prodNombre = $producto !== null ? $producto->getAttribute('nombre') : '';
            $prodData = $producto !== null ? new ProductoReporteData(nombre: $prodNombre) : null;

            if ($variante !== null) {
                /** @var string $varCodigo */
                $varCodigo = $variante->getAttribute('codigo');
                /** @var string $varNombre */
                $varNombre = $variante->getAttribute('nombre_variante');
                $varData = new VarianteReporteData(
                    codigo: $varCodigo,
                    nombre_variante: $varNombre
                );
            } else {
                $varData = null;
            }

            /** @var int $itemId */
            $itemId = $item->getAttribute('id');
            /** @var int $productoId */
            $productoId = $item->getAttribute('producto_id');
            /** @var float $cantidad */
            $cantidad = $item->getAttribute('cantidad');
            /** @var float $precioUnitario */
            $precioUnitario = $item->getAttribute('precio_unitario');
            /** @var float $subtotal */
            $subtotal = $item->getAttribute('subtotal');
            /** @var bool $esElegido */
            $esElegido = $item->getAttribute('es_elegido');

            return new CotizacionItemReporteData(
                id: $itemId,
                producto_id: $productoId,
                producto: $prodData,
                variante: $varData,
                cantidad: $cantidad,
                precio_unitario: $precioUnitario,
                subtotal: $subtotal,
                es_elegido: $esElegido
            );
        });
    }
}

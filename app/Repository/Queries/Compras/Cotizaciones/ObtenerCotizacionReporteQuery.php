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

        return $this->construirReporte($cotizacion);
    }

    private function construirReporte(Cotizacion $cotizacion): CotizacionReporteData
    {
        $cotId = $this->toInt($cotizacion->getAttribute('id'));
        $solId = $this->toInt($cotizacion->getAttribute('solicitud_id'));
        $provId = $this->toInt($cotizacion->getAttribute('proveedor_id'));
        $total = $this->toFloat($cotizacion->getAttribute('total'));
        $diasEntrega = $this->toInt($cotizacion->getAttribute('dias_entrega'));
        /** @var CarbonInterface|null $fechaCotizacion */
        $fechaCotizacion = $cotizacion->getAttribute('fecha_cotizacion');
        $esElegida = (bool) $cotizacion->getAttribute('es_elegida');
        $tasaCambioVal = $cotizacion->getAttribute('tasa_cambio');
        $tasaCambio = is_numeric($tasaCambioVal) ? (float) $tasaCambioVal : 1.0;
        $obsVal = $cotizacion->getAttribute('observaciones');
        $obsCotizacion = is_string($obsVal) ? $obsVal : null;

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

        /** @var Model|null $contactoPrincipal */
        $contactoPrincipal = $proveedor->relationLoaded('contactoPrincipal') ? $proveedor->getRelation('contactoPrincipal') : null;

        $cNombreVal = $contactoPrincipal?->getAttribute('nombre');
        $contactoNombre = is_string($cNombreVal) ? $cNombreVal : null;

        return new ProveedorReporteData(
            persona: $this->mapearPersona($persona),
            contacto_nombre: $contactoNombre
        );
    }

    private function mapearPersona(?Model $persona): ?PersonaReporteData
    {
        if ($persona === null) {
            return null;
        }

        /** @var Model|null $personaJuridica */
        $personaJuridica = $persona->relationLoaded('personaJuridica') ? $persona->getRelation('personaJuridica') : null;
        /** @var Model|null $personaNatural */
        $personaNatural = $persona->relationLoaded('personaNatural') ? $persona->getRelation('personaNatural') : null;

        $razVal = $personaJuridica?->getAttribute('razon_social');
        $razonSocialJuridica = is_string($razVal) ? $razVal : null;
        $nombreCompleto = $this->toString($persona->getAttribute('nombre_completo'));
        $razStr = $razonSocialJuridica ?? $nombreCompleto;

        $primerNombre = $this->toString($persona->getAttribute('primer_nombre'));
        $pApeVal = $personaNatural?->getAttribute('primer_apellido');
        $primerApellido = is_string($pApeVal) ? $pApeVal : null;

        return new PersonaReporteData(
            primer_nombre: $primerNombre,
            primer_apellido: $primerApellido,
            nombre_completo: $nombreCompleto,
            razon_social: $razStr
        );
    }

    private function mapearMoneda(?Moneda $moneda): ?MonedaReporteData
    {
        if ($moneda === null) {
            return null;
        }

        $codigo = $this->toString($moneda->getAttribute('codigo'));
        $simbolo = $this->toString($moneda->getAttribute('simbolo'));

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

        $solId = $this->toInt($solicitud->getAttribute('id'));
        $codigo = $this->toString($solicitud->getAttribute('codigo'));
        /** @var CarbonInterface|null $fechaSolicitud */
        $fechaSolicitud = $solicitud->getAttribute('fecha_solicitud');
        /** @var CarbonInterface|null $fechaNecesita */
        $fechaNecesita = $solicitud->getAttribute('fecha_necesita');
        $motVal = $solicitud->getAttribute('motivo');
        $motivo = is_string($motVal) ? $motVal : null;
        $notVal = $solicitud->getAttribute('notas');
        $notas = is_string($notVal) ? $notVal : null;

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
        return $itemsCollection->map(fn (CotizacionItem $item) => $this->mapearItem($item));
    }

    private function mapearItem(CotizacionItem $item): CotizacionItemReporteData
    {
        /** @var Model|null $producto */
        $producto = $item->relationLoaded('producto') ? $item->getRelation('producto') : null;
        /** @var Model|null $variante */
        $variante = $item->relationLoaded('variante') ? $item->getRelation('variante') : null;

        $itemId = $this->toInt($item->getAttribute('id'));
        $productoId = $this->toInt($item->getAttribute('producto_id'));
        $cantidad = $this->toFloat($item->getAttribute('cantidad'));
        $precioUnitario = $this->toFloat($item->getAttribute('precio_unitario'));
        $subtotal = $this->toFloat($item->getAttribute('subtotal'));
        $esElegido = (bool) $item->getAttribute('es_elegido');

        return new CotizacionItemReporteData(
            id: $itemId,
            producto_id: $productoId,
            producto: $this->mapearProducto($producto),
            variante: $this->mapearVariante($variante),
            cantidad: $cantidad,
            precio_unitario: $precioUnitario,
            subtotal: $subtotal,
            es_elegido: $esElegido
        );
    }

    private function mapearProducto(?Model $producto): ?ProductoReporteData
    {
        if ($producto === null) {
            return null;
        }

        $prodNombre = $this->toString($producto->getAttribute('nombre'));

        return new ProductoReporteData(nombre: $prodNombre);
    }

    private function mapearVariante(?Model $variante): ?VarianteReporteData
    {
        if ($variante === null) {
            return null;
        }

        $varCodigo = $this->toString($variante->getAttribute('codigo'));
        $varNombre = $this->toString($variante->getAttribute('nombre_variante'));

        return new VarianteReporteData(
            codigo: $varCodigo,
            nombre_variante: $varNombre
        );
    }

    private function toFloat(mixed $val): float
    {
        return is_numeric($val) ? (float) $val : 0.0;
    }

    private function toInt(mixed $val): int
    {
        return is_numeric($val) ? (int) $val : 0;
    }

    private function toString(mixed $val): string
    {
        return is_scalar($val) ? (string) $val : '';
    }
}

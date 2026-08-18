<?php

declare(strict_types=1);

namespace App\Interactors\Facturacion;

use App\Enums\Facturacion\EstadoFactura;
use App\Enums\Facturacion\EstadoFolioFactura;
use App\Enums\Facturacion\TipoFactura;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Monedas\TasaCambio;
use App\Repository\Persistencia\Facturacion\FacturaFolioPersistencia;
use App\Repository\Persistencia\Facturacion\FacturaPersistencia;
use App\Repository\Queries\Facturacion\FacturaQuery;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Monedas\ObtenerTasaCambioQuery;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class EmitirFacturaDesdeVenta
{
    public function __construct(
        private ReservarFolioFactura $reservarFolio,
        private ObtenerMonedaPredeterminadaQuery $obtenerMonedaPredeterminada,
        private ObtenerTasaCambioQuery $obtenerTasaCambio,
        private FacturaQuery $facturaQuery,
        private FacturaPersistencia $facturaPersistencia,
        private FacturaFolioPersistencia $facturaFolioPersistencia,
    ) {}

    /**
     * @param  array<string, mixed>  $datosReceptor
     */
    public function ejecutar(
        Venta $venta,
        ?int $serieId = null,
        TipoFactura $tipo = TipoFactura::Contado,
        array $datosReceptor = [],
        ?int $usuarioId = null,
    ): Factura {
        return DB::transaction(function () use ($venta, $serieId, $tipo, $datosReceptor, $usuarioId): Factura {
            $venta = $this->facturaQuery->ventaParaFacturaConLock($venta);

            if ($this->facturaQuery->yaEmitidaParaVenta($venta->id)) {
                throw new DomainException("La venta {$venta->numero_venta} ya tiene una factura emitida.");
            }

            $serie = $this->resolverSerie($serieId);
            $autorizacion = $this->resolverAutorizacion($serie);
            $folio = $this->reservarFolio->ejecutar($serie, $autorizacion, $usuarioId);

            $moneda = $venta->moneda ?? $this->monedaPredeterminada();
            $monedaBase = $this->monedaBase();
            $tasaRegistro = $this->resolverTasaCambio($moneda, $monedaBase);
            $tasa = $moneda->id === $monedaBase->id
                ? 1.0
                : (float) ($tasaRegistro !== null ? $tasaRegistro->tasa : 1.0);

            $factura = $this->facturaPersistencia->crear([
                'factura_serie_id' => $serie->id,
                'factura_autorizacion_dgi_id' => $autorizacion->id,
                'venta_id' => $venta->id,
                'cuenta_id' => $venta->cuenta_id,
                'cliente_id' => $venta->cliente_id,
                'tipo' => $tipo,
                'estado' => EstadoFactura::Emitida,
                'numero' => $folio->numero,
                'numero_correlativo' => $folio->numero_correlativo,
                'fecha_emision' => now(),
                'fecha_vencimiento' => $tipo === TipoFactura::Credito ? now()->addDays(30)->toDateString() : null,
                'moneda_id' => $moneda->id,
                'moneda_base_id' => $monedaBase->id,
                'tasa_cambio_id' => $tasaRegistro?->id,
                'tasa_cambio' => $tasa,
                'subtotal' => $venta->subtotal,
                'descuento_total' => $venta->descuento_total,
                'iva_total' => $venta->impuesto_total,
                'servicio_total' => $venta->servicio_total,
                'propina_total' => $venta->propina_total,
                'recargo_total' => $venta->recargo_total,
                'total' => $venta->total,
                'subtotal_base' => round((float) $venta->subtotal * $tasa, 2),
                'iva_total_base' => round((float) $venta->impuesto_total * $tasa, 2),
                'total_base' => round((float) $venta->total * $tasa, 2),
                'datos_receptor' => $datosReceptor !== [] ? $datosReceptor : ($venta->datos_fiscales ?? null),
                'numero_autorizacion_dgi' => $autorizacion->numero_autorizacion,
                'rango_autorizado_desde' => $autorizacion->rango_desde,
                'rango_autorizado_hasta' => $autorizacion->rango_hasta,
                'hash_documento' => hash('sha256', $folio->numero.'|'.$venta->id.'|'.now()->toISOString().'|'.Str::uuid()->toString()),
                'emitida_por' => $usuarioId,
            ]);

            $this->crearDetalles($factura, $venta);

            $this->facturaFolioPersistencia->actualizar($folio, [
                'factura_id' => $factura->id,
                'estado' => EstadoFolioFactura::Emitido,
                'emitido_at' => now(),
            ]);

            return $factura->load('detalles', 'serie', 'autorizacionDgi', 'folios');
        });
    }

    private function resolverSerie(?int $serieId): FacturaSerie
    {
        $serie = $this->facturaQuery->serieActiva($serieId);

        if ($serie === null) {
            throw new DomainException('No existe una serie de facturacion activa.');
        }

        return $serie;
    }

    private function resolverAutorizacion(FacturaSerie $serie): FacturaAutorizacionDgi
    {
        $autorizacion = $this->facturaQuery->autorizacionActivaSerie($serie);

        if ($autorizacion === null) {
            throw new DomainException("La serie {$serie->codigo} no tiene una autorizacion DGI activa.");
        }

        return $autorizacion;
    }

    private function monedaPredeterminada(): Moneda
    {
        return $this->obtenerMonedaPredeterminada->ejecutar()
            ?? throw new DomainException('No existe una moneda configurada en el sistema.');
    }

    private function monedaBase(): Moneda
    {
        return $this->monedaPredeterminada();
    }

    private function resolverTasaCambio(Moneda $moneda, Moneda $monedaBase): ?TasaCambio
    {
        if ($moneda->id === $monedaBase->id) {
            return null;
        }

        return $this->obtenerTasaCambio->ejecutarRegistro(
            now()->toDateString(),
            (string) $moneda->codigo,
            (string) $monedaBase->codigo,
        );
    }

    private function crearDetalles(Factura $factura, Venta $venta): void
    {
        $subtotalVenta = max(0.01, (float) $venta->subtotal);

        $venta->detalles->each(function (VentaDetalle $detalle) use ($factura, $venta, $subtotalVenta): void {
            $proporcion = (float) $detalle->subtotal / $subtotalVenta;
            $iva = (float) $detalle->impuesto > 0
                ? (float) $detalle->impuesto
                : round((float) $venta->impuesto_total * $proporcion, 2);
            $totalLinea = round((float) $detalle->subtotal - (float) $detalle->descuento + $iva, 2);

            $this->facturaPersistencia->crearDetalle($factura, [
                'venta_detalle_id' => $detalle->id,
                'concepto' => $detalle->concepto,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->subtotal,
                'descuento' => $detalle->descuento,
                'iva_porcentaje' => (float) $detalle->subtotal > 0 ? round(($iva / (float) $detalle->subtotal) * 100, 4) : 0,
                'iva' => $iva,
                'total_linea' => $totalLinea,
                'origen_type' => $detalle->origen_type,
                'origen_id' => $detalle->origen_id,
            ]);
        });
    }
}

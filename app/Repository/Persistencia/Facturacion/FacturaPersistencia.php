<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaDetalle;

final readonly class FacturaPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Factura
    {
        $ventaId = $datos['venta_id'] ?? null;
        $cuentaId = $datos['cuenta_id'] ?? null;
        if (is_numeric($ventaId) && is_numeric($cuentaId) && ! Venta::query()->whereKey((int) $ventaId)->where('cuenta_id', (int) $cuentaId)->exists()) {
            throw new \DomainException('La venta y la cuenta de la factura no corresponden a la misma operacion.');
        }
        /** @var Factura $factura */
        $factura = Factura::query()->create($datos);

        return $factura;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDetalle(Factura $factura, array $datos): FacturaDetalle
    {
        $lineaId = $datos['venta_detalle_id'] ?? null;
        if (is_numeric($lineaId) && ! VentaDetalle::query()->whereKey((int) $lineaId)->where('venta_id', $factura->venta_id)->exists()) {
            throw new \DomainException('La linea de venta no pertenece a la venta de esta factura.');
        }
        /** @var FacturaDetalle $detalle */
        $detalle = $factura->detalles()->create($datos);

        return $detalle;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function anular(Factura $factura, array $datos): Factura
    {
        $factura->update($datos);

        return $factura->refresh();
    }
}

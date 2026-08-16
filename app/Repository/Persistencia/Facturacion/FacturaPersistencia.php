<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaDetalle;

final readonly class FacturaPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Factura
    {
        /** @var Factura $factura */
        $factura = Factura::query()->create($datos);

        return $factura;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDetalle(Factura $factura, array $datos): FacturaDetalle
    {
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

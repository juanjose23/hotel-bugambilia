<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Ventas;

use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;

final class VentaRepositorio implements VentaRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Venta
    {
        return Venta::query()->create($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDetalle(Venta $venta, array $datos): VentaDetalle
    {
        /** @var VentaDetalle $detalle */
        $detalle = $venta->detalles()->create($datos);

        return $detalle;
    }
}

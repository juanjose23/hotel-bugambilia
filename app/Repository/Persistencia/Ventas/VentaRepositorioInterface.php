<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Ventas;

use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Cuentas\VentaDetalle;

interface VentaRepositorioInterface
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Venta;

    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearDetalle(Venta $venta, array $datos): VentaDetalle;
}

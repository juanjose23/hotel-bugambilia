<?php

declare(strict_types=1);

namespace App\Notifications\Compras\Contracts;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\DevolucionCompra;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;

interface UrlNotificadorInterface
{
    public function solicitud(Solicitud $solicitud): string;

    public function cotizacion(Cotizacion $cotizacion): string;

    public function ordenCompra(OrdenCompra $orden): string;

    public function recepcion(RecepcionCompra $recepcion): string;

    public function devolucion(DevolucionCompra $devolucion): string;
}

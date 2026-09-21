<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;

interface EjecucionLimpiezaRepositoryInterface
{
    public function asignarCarrito(LimpiezaEjecucion $ejecucion, int $carritoId): void;

    public function liberarCarrito(LimpiezaEjecucion $ejecucion): void;
}

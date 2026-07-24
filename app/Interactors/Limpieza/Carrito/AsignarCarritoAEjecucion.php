<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Limpieza\EjecucionLimpiezaRepository;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionLimpieza;

final class AsignarCarritoAEjecucion
{
    public function __construct(
        private readonly EjecucionLimpiezaRepository $repository,
        private readonly ObtenerEjecucionLimpieza $obtenerEjecucion,
    ) {}

    public function execute(int $ejecucionId, int $carritoId): LimpiezaEjecucion
    {
        $ejecucion = $this->obtenerEjecucion->execute($ejecucionId);
        $this->repository->asignarCarrito($ejecucion, $carritoId);

        return $ejecucion->refresh();
    }
}

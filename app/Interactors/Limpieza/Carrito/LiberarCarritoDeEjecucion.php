<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Limpieza\EjecucionLimpiezaRepositoryInterface;

final readonly class LiberarCarritoDeEjecucion
{
    public function __construct(
        private EjecucionLimpiezaRepositoryInterface $repository,
    ) {}

    public function execute(LimpiezaEjecucion $ejecucion): LimpiezaEjecucion
    {
        $this->repository->liberarCarrito($ejecucion);

        return $ejecucion->refresh();
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Persistencia\Limpieza\EjecucionLimpiezaRepository;
use App\Repository\Queries\Limpieza\Carrito\BloquearCarritoParaLimpieza;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionLimpieza;
use Illuminate\Support\Facades\DB;

final class AsignarCarritoAEjecucion
{
    public function __construct(
        private readonly EjecucionLimpiezaRepository $repository,
        private readonly ObtenerEjecucionLimpieza $obtenerEjecucion,
        private readonly BloquearCarritoParaLimpieza $bloquearCarrito,
    ) {}

    public function execute(int $ejecucionId, int $carritoId): LimpiezaEjecucion
    {
        return DB::transaction(function () use ($ejecucionId, $carritoId): LimpiezaEjecucion {
            $ejecucion = $this->obtenerEjecucion->execute($ejecucionId);
            $this->bloquearCarrito->execute($carritoId, $ejecucionId, $ejecucion->colaborador_id);
            $this->repository->asignarCarrito($ejecucion, $carritoId);

            return $ejecucion->refresh();
        });
    }
}

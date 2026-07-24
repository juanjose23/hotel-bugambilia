<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class BloquearCarritoParaLimpieza
{
    public function execute(int $carritoId, int $ejecucionId): void
    {
        $carrito = Ubicacion::query()
            ->whereKey($carritoId)
            ->where('tipo', 'carrito')
            ->lockForUpdate()
            ->first();

        if ($carrito === null) {
            throw new OperacionLimpiezaNoPermitida('El carrito seleccionado no existe o no está disponible.');
        }

        $perteneceAlTurno = LimpiezaEjecucion::query()
            ->whereKey($ejecucionId)
            ->whereHas('turno.carritos', fn ($query) => $query->whereKey($carritoId))
            ->exists();

        if (! $perteneceAlTurno) {
            throw new OperacionLimpiezaNoPermitida('El carrito seleccionado no pertenece al turno de esta tarea.');
        }

        $ocupado = LimpiezaEjecucion::query()
            ->where('estado', EstadoLimpieza::EnProgreso)
            ->where('carrito_id', $carritoId)
            ->whereKeyNot($ejecucionId)
            ->exists();

        if ($ocupado) {
            throw new OperacionLimpiezaNoPermitida('El carrito seleccionado ya está siendo utilizado.');
        }
    }
}

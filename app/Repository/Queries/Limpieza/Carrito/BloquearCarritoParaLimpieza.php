<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Catalogos\TipoUbicacion;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class BloquearCarritoParaLimpieza
{
    public function execute(int $carritoId, int $ejecucionId, ?int $colaboradorId = null): void
    {
        $carrito = Ubicacion::query()
            ->whereKey($carritoId)
            ->where(function ($q) {
                $q->where('tipo', TipoUbicacion::CARRITO->value)
                    ->orWhere('tipo', 'carrito')
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carrito%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carro%']);
            })
            ->lockForUpdate()
            ->first();

        if ($carrito === null) {
            throw new OperacionLimpiezaNoPermitida('El carrito seleccionado no existe o no está disponible.');
        }

        $ocupadoQuery = LimpiezaEjecucion::query()
            ->where('carrito_id', $carritoId)
            ->whereKeyNot($ejecucionId)
            ->where(function ($query): void {
                $query
                    ->where('estado', EstadoLimpieza::EnProgreso)
                    ->orWhere(function ($pendiente): void {
                        $pendiente
                            ->where('estado', EstadoLimpieza::Pendiente)
                            ->whereDate('fecha', now()->toDateString());
                    });
            });

        if ($colaboradorId !== null && $colaboradorId > 0) {
            $ocupadoQuery->where(function ($q) use ($colaboradorId) {
                $q->where('colaborador_id', '!=', $colaboradorId)
                    ->orWhereNull('colaborador_id');
            });
        }

        if ($ocupadoQuery->exists()) {
            throw new OperacionLimpiezaNoPermitida('El carrito seleccionado ya está siendo utilizado por otro colaborador.');
        }
    }
}

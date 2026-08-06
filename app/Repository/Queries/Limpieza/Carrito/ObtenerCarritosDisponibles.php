<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Catalogos\TipoUbicacion;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ObtenerCarritosDisponibles
{
    /**
     * Obtiene únicamente los carritos de limpieza que NO se encuentran en uso activo por otro colaborador.
     *
     * @return array<int, string>
     */
    public function execute(int $ejecucionId, ?int $colaboradorId = null): array
    {
        // 1. Obtener los IDs de todas las ubicaciones que son Carritos de Limpieza
        /** @var array<int> $carritosIds */
        $carritosIds = Ubicacion::query()
            ->where(function ($q) {
                $q->where('tipo', TipoUbicacion::CARRITO->value)
                    ->orWhere('tipo', 'carrito')
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carrito%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%carro%']);
            })
            ->pluck('id')
            ->toArray();

        if (empty($carritosIds)) {
            return [];
        }

        // 2. Obtener los IDs de los carritos ocupados por OTRO colaborador
        $busyQuery = LimpiezaEjecucion::query()
            ->whereNotNull('carrito_id')
            ->where('id', '!=', $ejecucionId)
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
            $busyQuery->where(function ($q) use ($colaboradorId) {
                $q->where('colaborador_id', '!=', $colaboradorId)
                    ->orWhereNull('colaborador_id');
            });
        }

        /** @var array<int> $busyCarritos */
        $busyCarritos = $busyQuery->pluck('carrito_id')->toArray();

        /** @var array<int, string> */
        return Ubicacion::query()
            ->where('estado', 1)
            ->whereIn('id', $carritosIds)
            ->whereNotIn('id', $busyCarritos)
            ->pluck('nombre', 'id')
            ->toArray();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerHistorialTransformacionesCocina
{
    /**
     * @return Collection<int, TransformacionMateriaPrima>
     */
    public function obtenerRecientes(int $limite = 15): Collection
    {
        /** @var Collection<int, TransformacionMateriaPrima> $registros */
        $registros = TransformacionMateriaPrima::query()
            ->with([
                'productoOrigen',
                'varianteOrigen',
                'ubicacionOrigen',
                'realizadoPor',
                'items.productoDestino',
                'items.varianteDestino',
                'items.ubicacionDestino',
            ])
            ->latest('id')
            ->take($limite)
            ->get();

        return $registros;
    }

    /**
     * @return LengthAwarePaginator<int, TransformacionMateriaPrima>
     */
    public function obtenerPaginados(int $porPagina = 10): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, TransformacionMateriaPrima> $paginados */
        $paginados = TransformacionMateriaPrima::query()
            ->with([
                'productoOrigen',
                'varianteOrigen',
                'ubicacionOrigen',
                'realizadoPor',
                'items.productoDestino',
                'items.varianteDestino',
                'items.ubicacionDestino',
            ])
            ->latest('id')
            ->paginate($porPagina);

        return $paginados;
    }
}

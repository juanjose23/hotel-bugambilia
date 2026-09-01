<?php

declare(strict_types=1);

namespace App\Interactors\Home;

use App\Repository\Queries\Espacios\ObtenerEspaciosHomeQuery;
use App\Repository\Queries\Habitaciones\ObtenerFiltrosHomeQuery;
use App\Repository\Queries\Habitaciones\ObtenerHabitacionesHomeQuery;
use App\Repository\Queries\Servicios\ObtenerServiciosHomeQuery;
use App\Support\HotelInfo;

final class ObtenerDatosHome
{
    public function __construct(
        private readonly ObtenerHabitacionesHomeQuery $habitacionesQuery,
        private readonly ObtenerFiltrosHomeQuery $filtrosQuery,
        private readonly ObtenerServiciosHomeQuery $serviciosQuery,
        private readonly ObtenerEspaciosHomeQuery $espaciosQuery,
    ) {}

    /**
     * @return array{
     *     hotelInfo: array<string, mixed>,
     *     habitaciones: array<int, array<string, mixed>>,
     *     servicios: array<int, array<string, mixed>>,
     *     espacios: array<int, array<string, mixed>>,
     *     filtrosDisponibles: array{
     *         categorias: array<int, array{id: int, nombre: string, slug: string, habitaciones_count: int}>,
     *         vistas: array<int, string>,
     *         servicios: array<int, array{id: int, nombre: string, slug: string, categoria: string}>,
     *         capacidades: array<int, int>,
     *         precioMin: float,
     *         precioMax: float
     *     }
     * }
     */
    public function ejecutar(): array
    {
        return [
            'hotelInfo' => HotelInfo::getInfo(),
            'habitaciones' => $this->habitacionesQuery->ejecutar(),
            'servicios' => $this->serviciosQuery->ejecutar(),
            'espacios' => $this->espaciosQuery->ejecutar(),
            'filtrosDisponibles' => $this->filtrosQuery->ejecutar(),
        ];
    }
}

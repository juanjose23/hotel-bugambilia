<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios\Reportes;

use App\Enums\Catalogos\CatalogoTipo;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Support\CachedOptions;

final class ObtenerMetricasReporteServiciosQuery
{
    /**
     * @return array{
     *   totalServicios: int,
     *   preciosVigentes: int,
     *   totalMonedas: int,
     *   totalCategorias: int
     * }
     */
    public function ejecutar(): array
    {
        return [
            'totalServicios' => Servicio::count(),
            'preciosVigentes' => Precio::where('priceable_type', Servicio::class)->where('estado', 1)->count(),
            'totalMonedas' => Moneda::count(),
            'totalCategorias' => CachedOptions::catalogos(CatalogoTipo::CATEGORIA_SERVICIO->value)->count(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Database\Eloquent\Collection;

final readonly class ObtenerHabitacionDetalleWebQuery
{
    public function resolverHabitacion(string $slug): ?Habitacion
    {
        $query = Habitacion::query()->with([
            'categoria', 'ubicacion', 'detalle', 'imagenes', 'precios.moneda',
            'politicas.penalizaciones', 'servicioAsignaciones.servicio', 'inventarioFijo.activo.producto.categoria',
        ])->activas();

        if (ctype_digit($slug)) {
            /** @var Habitacion|null $habitacion */
            $habitacion = $query->find((int) $slug);

            return $habitacion;
        }

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            /** @var Habitacion|null $habitacion */
            $habitacion = (clone $query)->find((int) $matches[1]);
            if ($habitacion !== null) {
                return $habitacion;
            }
        }

        /** @var Habitacion|null $habitacion */
        $habitacion = $query->where('slug', $slug)->orWhere('codigo', $slug)->first();

        return $habitacion;
    }

    /**
     * @return Collection<int, Servicio>
     */
    public function obtenerServiciosDisponiblesWeb(): Collection
    {
        return Servicio::query()
            ->activos()
            ->where('web', true)
            ->with(['precios.moneda'])
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Presenters\Landing\HabitacionDetallePresenter;
use App\Repository\Models\Habitaciones\Habitacion;

final class ObtenerHabitacionDetalleLanding
{
    public function __construct(
        private readonly HabitacionDetallePresenter $presenter,
    ) {}

    /**
     * @return array{room: array<string, mixed>, similarRooms: array<int, array<string, mixed>>}
     */
    public function ejecutar(string $slug): array
    {
        $habitacion = $this->resolverHabitacion($slug);

        if (! $habitacion instanceof Habitacion) {
            abort(404, 'Habitación no encontrada.');
        }

        return [
            'room' => $this->presenter->detalle($habitacion),
            'similarRooms' => $this->presenter->similares($habitacion),
        ];
    }

    private function resolverHabitacion(string $slug): ?Habitacion
    {
        $query = Habitacion::with([
            'categoria', 'ubicacion', 'detalle', 'imagenes', 'precios.moneda',
            'politicas', 'servicioAsignaciones.servicio', 'inventarioFijo.activo',
        ])->activas();

        if (ctype_digit($slug)) {
            return $query->find((int) $slug);
        }

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $habitacion = (clone $query)->find((int) $matches[1]);
            if ($habitacion !== null) {
                return $habitacion;
            }
        }

        return $query->where('slug', $slug)->orWhere('codigo', $slug)->first();
    }
}

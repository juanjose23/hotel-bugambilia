<?php

declare(strict_types=1);

namespace App\Repository\Queries\Habitaciones;

use App\Repository\Models\Habitaciones\Habitacion;

final class ObtenerHabitacionesHomeQuery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(int $limite = 12): array
    {
        $habitaciones = Habitacion::with([
            'categoria',
            'detalle',
            'imagenes',
            'precios.moneda',
            'servicioAsignaciones.servicio',
        ])
            ->activas()
            ->orderBy('id', 'asc')
            ->take($limite)
            ->get();

        return $habitaciones->map(fn (Habitacion $h): array => $this->mapear($h))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapear(Habitacion $h): array
    {
        $precioObj = $h->precios->first();
        $precio = $precioObj !== null ? (float) $precioObj->precio : 0.00;
        $moneda = $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$';

        $detalle = $h->detalle;
        $capacidad = $detalle ? (int) ($detalle->capacidad_adultos + $detalle->capacidad_ninos) : 2;

        $imagenes = $h->imagenes->map(function ($img): ?string {
            $url = trim((string) $img->url);
            if ($url === '') {
                return null;
            }
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                return $url;
            }

            return '/storage/'.ltrim($url, '/');
        })->filter()->values()->all();

        $servicios = $h->servicioAsignaciones
            ->map(fn ($sa) => $sa->servicio)
            ->filter()
            ->map(fn ($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
            ])
            ->values()
            ->all();

        $serviciosIds = array_column($servicios, 'id');

        return [
            'id' => $h->id,
            'codigo' => $h->codigo,
            'numero' => $h->numero,
            'slug' => $h->slug,
            'nombre' => $h->nombre,
            'descripcion' => $h->descripcion ?? '',
            'categoria' => $h->categoria->nombre ?? '',
            'precio' => $precio,
            'precio_desde' => $precio,
            'moneda' => $moneda,
            'capacidad' => $capacidad,
            'imagen' => $imagenes[0] ?? null,
            'imagenes' => $imagenes,
            'servicios' => $servicios,
            'servicios_ids' => $serviciosIds,
        ];
    }
}

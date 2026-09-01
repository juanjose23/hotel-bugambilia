<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios;

use App\Repository\Models\Servicios\Servicio;

final class ObtenerServiciosHomeQuery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(int $limite = 4): array
    {
        $servicios = Servicio::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activos()
            ->where('web', true)
            ->take($limite)
            ->get();

        return $servicios->map(fn (Servicio $s): array => $this->mapear($s))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapear(Servicio $s): array
    {
        $precioObj = $s->precios->first();
        $imagen = $s->imagenes->first();

        $urlImagen = null;
        if ($imagen && ! empty($imagen->url)) {
            $url = trim((string) $imagen->url);
            $urlImagen = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/'))
                ? $url
                : '/storage/'.ltrim($url, '/');
        }

        return [
            'id' => $s->id,
            'codigo' => $s->codigo,
            'slug' => $s->slug ?? (string) $s->id,
            'nombre' => $s->nombre,
            'descripcion' => $s->descripcion ?? '',
            'categoria' => $s->categoria->nombre ?? '',
            'precio' => $precioObj ? (float) $precioObj->precio : null,
            'moneda' => $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$',
            'imagen' => $urlImagen ?? '',
        ];
    }
}

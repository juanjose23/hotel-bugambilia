<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Repository\Models\Servicios\Servicio;
use Illuminate\Support\Str;

final class ServicioTarjetaPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function tarjeta(Servicio $servicio): array
    {
        $precio = $this->precio($servicio);
        $categoriaNombre = $servicio->categoria->nombre ?? 'Servicio General';

        return [
            'id' => $servicio->id,
            'codigo' => $servicio->codigo,
            'slug' => Str::slug($servicio->nombre).'-'.$servicio->id,
            'nombre' => $servicio->nombre,
            'descripcion' => $servicio->descripcion ?? 'Servicio exclusivo para nuestros huéspedes.',
            'categoria' => $categoriaNombre,
            'precio' => $precio['precio'],
            'moneda' => $precio['moneda'],
            'imagen' => $this->imagen($servicio, $categoriaNombre),
            'icono' => $servicio->icono,
        ];
    }

    /**
     * @return array{precio: float|null, moneda: string}
     */
    private function precio(Servicio $servicio): array
    {
        $precioObj = $servicio->precios->first();
        $moneda = $precioObj?->moneda;

        return [
            'precio' => $precioObj ? (float) $precioObj->precio : null,
            'moneda' => $moneda ? $moneda->simbolo : '$',
        ];
    }

    private function imagen(Servicio $servicio, string $categoriaNombre): string
    {
        $imagen = $servicio->imagenes->first();

        if ($imagen !== null && ! empty($imagen->url)) {
            $url = trim($imagen->url);

            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                return $url;
            }

            return '/storage/'.ltrim($url, '/');
        }

        return $this->imagenPorCategoria($categoriaNombre, $servicio->nombre);
    }

    private function imagenPorCategoria(string $categoriaNombre, string $nombre): string
    {
        $cat = strtolower($categoriaNombre);
        $name = strtolower($nombre);

        if (str_contains($cat, 'gastro') || str_contains($cat, 'restaurante') || str_contains($name, 'desayuno') || str_contains($name, 'cena')) {
            return '/images/service-kitchen.png';
        }

        if (str_contains($cat, 'piscina') || str_contains($name, 'piscina') || str_contains($cat, 'agua')) {
            return '/images/service-pool.png';
        }

        if (str_contains($cat, 'bar') || str_contains($cat, 'bebida') || str_contains($name, 'bar')) {
            return '/images/service-bartender.png';
        }

        if (str_contains($cat, 'evento') || str_contains($name, 'evento')) {
            return '/images/service-events.png';
        }

        return '/images/terrace.jpg';
    }
}

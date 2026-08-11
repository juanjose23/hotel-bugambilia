<?php

declare(strict_types=1);

namespace App\Actions\Landing;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;

final class ResolverUrlImagen
{
    public function ejecutar(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return '/storage/'.ltrim($url, '/');
    }

    /**
     * URLs normalizadas (prefijo /storage) para la vista detalle.
     *
     * @return array<int, string>
     */
    public function deEspacio(Espacio $espacio): array
    {
        $urls = [];

        foreach ($espacio->imagenes as $imagen) {
            $url = $this->ejecutar($imagen->url);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls !== [] ? $urls : $this->porDefectoEspacio($espacio->tipo->value);
    }

    /**
     * URLs crudas tal como están almacenadas para la vista de lista.
     *
     * @return array<int, string>
     */
    public function listaEspacio(Espacio $espacio): array
    {
        $urls = [];

        foreach ($espacio->imagenes as $imagen) {
            if ($imagen->url !== '') {
                $urls[] = $imagen->url;
            }
        }

        return $urls !== [] ? $urls : $this->porDefectoEspacio($espacio->tipo->value);
    }

    /**
     * URLs normalizadas (prefijo /storage) para la vista detalle.
     *
     * @return array<int, string>
     */
    public function deHabitacion(Habitacion $habitacion): array
    {
        $urls = [];

        foreach ($habitacion->imagenes as $imagen) {
            $url = $this->ejecutar($imagen->url);
            if ($url !== null) {
                $urls[] = $url;
            }
        }

        return $urls !== [] ? $urls : ['/images/group-room.jpg', '/images/main-room.jpg', '/images/room-detail.jpg', '/images/terrace.jpg'];
    }

    /** @return array<int, string> */
    public function porDefectoEspacio(string $tipo): array
    {
        return match ($tipo) {
            'restaurante', 'bar' => ['/images/service-kitchen.png', '/images/service-bartender.png'],
            'piscina' => ['/images/service-pool.png', '/images/terrace.jpg'],
            'salon' => ['/images/service-events.png', '/images/room-detail.jpg'],
            default => ['/images/terrace.jpg', '/images/main-room.jpg'],
        };
    }
}

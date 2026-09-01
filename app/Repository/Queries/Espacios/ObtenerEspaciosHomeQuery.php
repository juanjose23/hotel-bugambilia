<?php

declare(strict_types=1);

namespace App\Repository\Queries\Espacios;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;

final class ObtenerEspaciosHomeQuery
{
    /**
     * @return array<int, array{
     *     id: int,
     *     codigo: string,
     *     slug: string,
     *     nombre: string,
     *     descripcion: string,
     *     tipo: string,
     *     categoria: string,
     *     capacidad_personas: int,
     *     imagen: string
     * }>
     */
    public function ejecutar(): array
    {
        $espacios = Espacio::with(['imagenes', 'ubicacion'])
            ->whereNull('padre_id')
            ->where('estado', '!=', EstadoEspacio::Inactivo)
            ->where('web', true)
            ->orderBy('orden')
            ->get();

        return $espacios->map(function (Espacio $espacio): array {
            return $this->mapear($espacio);
        })->values()->all();
    }

    /**
     * @return array{
     *     id: int,
     *     codigo: string,
     *     slug: string,
     *     nombre: string,
     *     descripcion: string,
     *     tipo: string,
     *     categoria: string,
     *     capacidad_personas: int,
     *     imagen: string
     * }
     */
    private function mapear(Espacio $espacio): array
    {
        $imagenRel = $espacio->imagenes->first();
        $urlImagen = null;

        if ($imagenRel && ! empty($imagenRel->url)) {
            $url = trim((string) $imagenRel->url);
            $urlImagen = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/'))
                ? $url
                : '/storage/'.ltrim($url, '/');
        }

        $tipoValor = $espacio->tipo->value;

        return [
            'id' => (int) $espacio->id,
            'codigo' => (string) $espacio->codigo,
            'slug' => (string) ($espacio->slug ?? (string) $espacio->id),
            'nombre' => (string) $espacio->nombre,
            'descripcion' => (string) ($espacio->descripcion ?? ''),
            'tipo' => $tipoValor,
            'categoria' => $this->formatearCategoria($tipoValor),
            'capacidad_personas' => (int) ($espacio->capacidad_personas ?? 0),
            'imagen' => $urlImagen ?? '',
        ];
    }

    private function formatearCategoria(string $tipo): string
    {
        return match ($tipo) {
            'restaurante' => 'Gastronomía & Bar',
            'salon' => 'Eventos & Reuniones',
            'gym' => 'Fitness & Bienestar',
            'spa' => 'Relajación & Masajes',
            'terraza' => 'Área Libre & Solárium',
            default => 'Espacio',
        };
    }
}

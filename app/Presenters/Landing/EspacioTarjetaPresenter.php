<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Actions\Landing\ResolverUrlImagen;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Str;

final class EspacioTarjetaPresenter
{
    public function __construct(
        private readonly ResolverUrlImagen $resolverUrlImagen,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function tarjeta(Espacio $espacio): array
    {
        $tipo = $espacio->tipo;

        return [
            'id' => $espacio->id,
            'codigo' => $espacio->codigo,
            'slug' => $espacio->slug ?? Str::slug($espacio->nombre),
            'nombre' => $espacio->nombre,
            'tipo' => $tipo->value,
            'tipo_label' => $tipo->getLabel(),
            'capacidad' => $espacio->capacidad_personas ?? 1,
            'descripcion' => ! empty($espacio->descripcion) ? $espacio->descripcion : 'Sin descripción detallada disponible.',
            'ubicacion' => $espacio->ubicacion->nombre ?? 'Instalaciones Principales',
            'web' => (bool) $espacio->web,
            'reservable' => (bool) $espacio->reservable,
            'imagenes' => $this->resolverUrlImagen->listaEspacio($espacio),
            'es_restaurante' => $tipo->value === 'restaurante',
            'meta_datos' => $espacio->meta_datos ?? [],
            'sub_espacios' => $espacio->hijos
                ->map(fn (Espacio $hijo): array => [
                    'id' => $hijo->id,
                    'codigo' => $hijo->codigo,
                    'slug' => $hijo->slug ?? Str::slug($hijo->nombre),
                    'nombre' => $hijo->nombre,
                    'capacidad' => $hijo->capacidad_personas ?? 1,
                    'reservable' => (bool) $hijo->reservable,
                ])
                ->values()
                ->all(),
        ];
    }
}

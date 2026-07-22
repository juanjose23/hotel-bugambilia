<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Espacios\Espacio;

final class ObtenerEspaciosLanding
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function ejecutar(?string $categoriaTipo = null): array
    {
        $query = Espacio::activosWeb()
            ->whereNull('padre_id')
            ->with(['imagenes', 'ubicacion', 'hijos'])
            ->orderBy('orden')
            ->orderBy('nombre');

        if ($categoriaTipo !== null && trim($categoriaTipo) !== '' && strtoupper(trim($categoriaTipo)) !== 'TODOS') {
            $query->where('tipo', trim($categoriaTipo));
        }

        /** @var array<int, array<string, mixed>> $result */
        $result = $query->get()->map(function (Espacio $espacio): array {
            $imagenes = $espacio->imagenes->pluck('url')->filter()->values()->toArray();
            if (empty($imagenes)) {
                $imagenes = match ($espacio->tipo->value) {
                    'restaurante', 'bar' => ['/images/service-kitchen.png', '/images/service-bartender.png'],
                    'piscina' => ['/images/service-pool.png', '/images/terrace.jpg'],
                    'salon' => ['/images/service-events.png', '/images/room-detail.jpg'],
                    default => ['/images/terrace.jpg', '/images/main-room.jpg'],
                };
            }

            $tipoStr = $espacio->tipo->value;
            $tipoLabel = $espacio->tipo->getLabel();

            return [
                'id' => $espacio->id,
                'codigo' => $espacio->codigo,
                'nombre' => $espacio->nombre,
                'tipo' => $tipoStr,
                'tipo_label' => $tipoLabel,
                'capacidad' => $espacio->capacidad_personas ?? 1,
                'descripcion' => ! empty($espacio->descripcion) ? $espacio->descripcion : 'Sin descripción detallada disponible.',
                'ubicacion' => $espacio->ubicacion->nombre ?? 'Instalaciones Principales',
                'web' => (bool) $espacio->web,
                'reservable' => (bool) $espacio->reservable,
                'imagenes' => $imagenes,
                'es_restaurante' => $tipoStr === 'restaurante',
                'meta_datos' => $espacio->meta_datos ?? [],
            ];
        })->values()->all();

        return $result;
    }

    /**
     * @return array<int, array{tipo: string, label: string}>
     */
    public function tiposDisponibles(): array
    {
        $espacios = Espacio::activosWeb()->whereNull('padre_id')->get();

        $tipos = [];
        foreach ($espacios as $e) {
            $tipoStr = $e->tipo->value;
            $label = $e->tipo->getLabel();

            if (! isset($tipos[$tipoStr])) {
                $tipos[$tipoStr] = ['tipo' => $tipoStr, 'label' => $label];
            }
        }

        return array_values($tipos);
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Shared\ServicioAsignacion;

final class ObtenerEspacioDetalleLanding
{
    /**
     * @return array{space: array<string, mixed>, similarSpaces: array<int, array<string, mixed>>}
     */
    public function ejecutar(int $id): array
    {
        $espacio = Espacio::with([
            'ubicacion', 'imagenes', 'precios.moneda', 'politicas', 'servicioAsignaciones.servicio',
        ])->activosWeb()->find($id);

        if (! $espacio instanceof Espacio) {
            abort(404, 'Espacio no encontrado.');
        }

        $precioObj = $espacio->precios->first();
        $imagenesUrls = $this->resolverImagenes($espacio);
        $serviciosIncluidos = $this->resolverServicios($espacio);
        $politicasData = $this->formatearPoliticas($espacio);

        $tipoStr = $espacio->tipo->value;
        $tipoLabel = $espacio->tipo->getLabel();

        return [
            'space' => [
                'id' => $espacio->id,
                'codigo' => $espacio->codigo,
                'nombre' => $espacio->nombre,
                'tipo' => $tipoStr,
                'tipo_label' => $tipoLabel,
                'descripcion' => ! empty($espacio->descripcion) ? $espacio->descripcion : 'Sin descripción detallada disponible en este momento.',
                'ubicacion' => $espacio->ubicacion->nombre ?? 'Instalaciones Principales',
                'precio' => $precioObj ? (float) $precioObj->precio : 0.0,
                'moneda' => $precioObj->moneda->simbolo ?? 'C$',
                'capacidad' => $espacio->capacidad_personas ?? 1,
                'web' => (bool) $espacio->web,
                'reservable' => (bool) $espacio->reservable,
                'es_restaurante' => $tipoStr === 'restaurante',
                'imagenes' => $imagenesUrls,
                'meta_datos' => $espacio->meta_datos ?? [],
                'serviciosIncluidos' => $serviciosIncluidos,
                'politicas' => $politicasData,
            ],
            'similarSpaces' => $this->resolverSimilares($espacio),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolverImagenes(Espacio $espacio): array
    {
        /** @var array<int, string> $urls */
        $urls = $espacio->imagenes->map(function ($img): string {
            $url = trim((string) $img->url);

            return match (true) {
                str_starts_with($url, 'http://'), str_starts_with($url, 'https://'), str_starts_with($url, '/') => $url,
                default => '/storage/'.ltrim($url, '/'),
            };
        })->values()->toArray();

        if ($urls !== []) {
            return $urls;
        }

        return match ($espacio->tipo->value) {
            'restaurante', 'bar' => ['/images/service-kitchen.png', '/images/service-bartender.png'],
            'piscina' => ['/images/service-pool.png', '/images/terrace.jpg'],
            'salon' => ['/images/service-events.png', '/images/room-detail.jpg'],
            default => ['/images/terrace.jpg', '/images/main-room.jpg'],
        };
    }

    /**
     * @return array<int, string>
     */
    private function resolverServicios(Espacio $espacio): array
    {
        /** @var array<int, string> $servicios */
        $servicios = $espacio->servicioAsignaciones
            ->map(fn (ServicioAsignacion $sa) => $sa->servicio?->nombre)
            ->filter()
            ->values()
            ->toArray();

        if ($servicios !== []) {
            return $servicios;
        }

        // Fallbacks por tipo
        return match ($espacio->tipo->value) {
            'salon' => [
                'Sistema de audio profesional integrado',
                'Proyector de alta definición y telón',
                'Mobiliario configurable (mesas, sillas)',
                'Servicio de catering disponible a solicitud',
                'Conexión Wi-Fi de alta velocidad dedicada',
                'Aire acondicionado regulable central',
            ],
            'piscina' => [
                'Uso de camastros y toallas limpias',
                'Servicio de bar junto a la alberca',
                'Vestidores, regaderas y lockers privados',
                'Área techada para descanso',
                'Acceso al bar lounge contiguo',
            ],
            default => [
                'Conexión Wi-Fi de cortesía',
                'Atención y asistencia personalizada',
                'Ubicación privilegiada en las instalaciones',
                'Servicio de alimentos y bebidas bajo pedido',
            ],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatearPoliticas(Espacio $espacio): array
    {
        /** @var array<int, array<string, mixed>> $politicas */
        $politicas = $espacio->politicas->map(fn ($p): array => [
            'id' => $p->id,
            'nombre' => (string) ($p->nombre ?? ''),
            'descripcion' => $p->descripcion,
            'tipo' => 'Politica',
        ])->values()->toArray();

        if ($politicas !== []) {
            return $politicas;
        }

        return [
            ['nombre' => 'Reserva Anticipada', 'descripcion' => 'Recomendamos realizar su reserva con al menos 24 horas de anticipación para asegurar disponibilidad.'],
            ['nombre' => 'Política de Cancelación', 'descripcion' => 'Cancelación gratuita realizando la solicitud con al menos 24 horas de antelación.'],
            ['nombre' => 'Normativa de Convivencia', 'descripcion' => 'Se solicita mantener niveles moderados de ruido para asegurar la paz de todos los huéspedes.'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolverSimilares(Espacio $espacio): array
    {
        /** @var array<int, array<string, mixed>> $similares */
        $similares = Espacio::with(['precios.moneda', 'imagenes'])
            ->activosWeb()
            ->whereNull('padre_id')
            ->where('id', '!=', $espacio->id)
            ->take(3)
            ->get()
            ->map(function ($e): array {
                $p = $e->precios->first();
                $img = $e->imagenes->first();
                $imgUrl = $img
                    ? (str_starts_with($img->url, '/') ? $img->url : '/storage/'.ltrim($img->url, '/'))
                    : '/images/terrace.jpg';

                $tipoStr = $e->tipo->value;

                return [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'tipo' => $tipoStr,
                    'precio' => $p ? (float) $p->precio : 0.0,
                    'moneda' => $p->moneda->simbolo ?? 'C$',
                    'imagen' => $imgUrl,
                ];
            })
            ->toArray();

        return $similares;
    }
}

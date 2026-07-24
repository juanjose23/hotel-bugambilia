<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Support\Str;

final class ObtenerEspacioDetalleLanding
{
    /**
     * @return array{space: array<string, mixed>, similarSpaces: array<int, array<string, mixed>>}
     */
    public function ejecutar(string|int $identificador): array
    {
        $espacio = Espacio::with([
            'ubicacion', 'imagenes', 'precios.moneda', 'politicas', 'servicioAsignaciones.servicio',
        ])->activosWeb()
            ->where(function ($q) use ($identificador): void {
                $q->where('slug', (string) $identificador);
                if (is_numeric($identificador)) {
                    $q->orWhere('id', (int) $identificador);
                }
            })
            ->first();

        if (! $espacio instanceof Espacio) {
            abort(404, 'Espacio no encontrado.');
        }

        $preciosOrdenados = $espacio->precios->sortByDesc('es_oferta');

        $precioHoraObj = $preciosOrdenados->first(fn ($p) => $p->tipo_precio->value === 'por_hora');
        $precioBaseObj = $preciosOrdenados->first(fn ($p) => $p->tipo_precio->value === 'base');
        $precioObj = $precioHoraObj ?? $precioBaseObj ?? $preciosOrdenados->first();

        $precioPorHora = $precioHoraObj ? (float) $precioHoraObj->precio : 0.0;
        $precioBase = $precioBaseObj ? (float) $precioBaseObj->precio : 0.0;

        $esOferta = $precioObj !== null ? (bool) $precioObj->es_oferta : false;
        $precioPrincipal = $precioPorHora > 0 ? $precioPorHora : $precioBase;
        $tipoTarifaLabel = $precioPorHora > 0 ? '/ hora' : ($precioBase > 0 ? '/ evento' : '');

        $imagenesUrls = $this->resolverImagenes($espacio);
        $serviciosIncluidos = $this->resolverServicios($espacio);
        $politicasData = $this->formatearPoliticas($espacio);

        $tipoStr = $espacio->tipo->value;
        $tipoLabel = $espacio->tipo->getLabel();

        return [
            'space' => [
                'id' => $espacio->id,
                'codigo' => $espacio->codigo,
                'slug' => $espacio->slug ?? Str::slug($espacio->nombre),
                'nombre' => $espacio->nombre,
                'tipo' => $tipoStr,
                'tipo_label' => $tipoLabel,
                'descripcion' => ! empty($espacio->descripcion) ? $espacio->descripcion : 'Sin descripción detallada disponible en este momento.',
                'ubicacion' => $espacio->ubicacion->nombre ?? 'Instalaciones Principales',
                'precio' => $precioPrincipal,
                'precio_por_hora' => $precioPorHora,
                'precio_base' => $precioBase,
                'es_oferta' => $esOferta,
                'tipo_tarifa_label' => $tipoTarifaLabel,
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
     * @return array<int, array<string, mixed>>
     */
    private function resolverServicios(Espacio $espacio): array
    {
        /** @var array<int, array<string, mixed>> $servicios */
        $servicios = $espacio->servicioAsignaciones
            ->map(fn (ServicioAsignacion $sa): array => [
                'nombre' => (string) ($sa->servicio !== null ? $sa->servicio->nombre : ''),
                'descripcion' => (string) ($sa->servicio !== null ? ($sa->servicio->descripcion ?? '') : ''),
                'icono' => (string) ($sa->servicio !== null ? ($sa->servicio->icono ?? '') : ''),
                'incluido' => (bool) $sa->incluido,
            ])
            ->filter(fn (array $s): bool => $s['nombre'] !== '')
            ->values()
            ->toArray();

        return $servicios;
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

        return $politicas;
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
                    'slug' => $e->slug ?? Str::slug($e->nombre),
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

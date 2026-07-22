<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Servicios\Servicio;
use Illuminate\Support\Str;

final class ObtenerServicioDetalleLanding
{
    /**
     * @return array{service: array<string, mixed>}
     */
    public function ejecutar(string $slug): array
    {
        $servicio = $this->resolverServicio($slug);

        if (! $servicio instanceof Servicio) {
            abort(404, 'Servicio no encontrado.');
        }

        $precioObj = $servicio->precios->first();
        $imagenesUrls = $this->resolverImagenes($servicio);
        $politicasData = $this->formatearPoliticas($servicio);

        return [
            'service' => [
                'id' => $servicio->id,
                'codigo' => $servicio->codigo,
                'slug' => Str::slug($servicio->nombre).'-'.$servicio->id,
                'nombre' => $servicio->nombre,
                'descripcion' => $servicio->descripcion,
                'categoria' => $servicio->categoria->nombre ?? 'Servicio General',
                'precio' => $precioObj ? (float) $precioObj->precio : null,
                'moneda' => $precioObj->moneda->simbolo ?? '$',
                'imagenes' => $imagenesUrls,
                'icono' => $servicio->icono,
                'politicas' => $politicasData,
            ],
        ];
    }

    private function resolverServicio(string $slug): ?Servicio
    {
        $query = Servicio::with(['categoria', 'imagenes', 'precios.moneda', 'politicas'])->activos()->where('web', true);

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            return $query->find((int) $matches[1]);
        }

        return $query->where('codigo', $slug)->first();
    }

    /**
     * @return array<int, string>
     */
    private function resolverImagenes(Servicio $servicio): array
    {
        /** @var array<int, string> $urls */
        $urls = $servicio->imagenes->map(function ($img): string {
            $url = trim((string) $img->url);

            return match (true) {
                str_starts_with($url, 'http://'), str_starts_with($url, 'https://'), str_starts_with($url, '/') => $url,
                default => '/storage/'.ltrim($url, '/'),
            };
        })->values()->toArray();

        if ($urls !== []) {
            return $urls;
        }

        $cat = strtolower($servicio->categoria->nombre ?? '');
        $name = strtolower($servicio->nombre);

        return [match (true) {
            str_contains($cat, 'gastro') || str_contains($cat, 'restaurante') || str_contains($name, 'desayuno') || str_contains($name, 'cena') => '/images/service-kitchen.png',
            str_contains($cat, 'piscina') || str_contains($name, 'piscina') => '/images/service-pool.png',
            str_contains($cat, 'bar') || str_contains($name, 'bar') => '/images/service-bartender.png',
            str_contains($cat, 'evento') || str_contains($name, 'evento') => '/images/service-events.png',
            default => '/images/terrace.jpg',
        }];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatearPoliticas(Servicio $servicio): array
    {
        /** @var array<int, array<string, mixed>> $politicas */
        $politicas = $servicio->politicas->map(fn ($p): array => [
            'id' => $p->id,
            'nombre' => (string) ($p->nombre ?? ''),
            'descripcion' => $p->descripcion,
            'tipo' => 'Politica',
        ])->values()->toArray();

        return $politicas;
    }
}

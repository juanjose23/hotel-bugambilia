<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Activos\ActivoAsignacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Shared\ServicioAsignacion;
use Illuminate\Support\Str;

final class ObtenerHabitacionDetalleLanding
{
    /**
     * @return array{room: array<string, mixed>, similarRooms: array<int, array<string, mixed>>}
     */
    public function ejecutar(string $slug): array
    {
        $habitacion = $this->resolverHabitacion($slug);

        if (! $habitacion instanceof Habitacion) {
            abort(404, 'Habitación no encontrada.');
        }

        $precioObj = $habitacion->precios->first();
        $imagenesUrls = $this->resolverImagenes($habitacion);
        $capacidades = $this->resolverCapacidades($habitacion);
        $serviciosIncluidos = $this->resolverServicios($habitacion);
        $politicasData = $this->formatearPoliticas($habitacion);
        $equipamiento = $this->resolverEquipamiento($habitacion);

        $nombre = $habitacion->nombre ?? "Habitación $habitacion->numero";

        return [
            'room' => [
                'id' => $habitacion->id,
                'codigo' => $habitacion->codigo,
                'numero' => $habitacion->numero,
                'slug' => Str::slug($nombre).'-'.$habitacion->id,
                'nombre' => $nombre,
                'descripcion' => $habitacion->descripcion ?? 'Ambiente confortable con acabados de primera calidad, pensado para su descanso en Estelí.',
                'categoria' => $habitacion->categoria->nombre ?? 'Suite Elegante',
                'ubicacion' => $habitacion->ubicacion->nombre ?? 'Piso Principal',
                'precio' => $precioObj ? (float) $precioObj->precio : 45.0,
                'moneda' => $precioObj->moneda->simbolo ?? '$',
                'capacidad' => $capacidades['total'],
                'adultos' => $capacidades['adultos'],
                'ninos' => $capacidades['ninos'],
                'medidas' => $habitacion->detalle?->medidas ? $habitacion->detalle->medidas.' m²' : '32 m²',
                'vistas' => $habitacion->detalle && is_array($habitacion->detalle->vistas) && $habitacion->detalle->vistas !== [] ? $habitacion->detalle->vistas : ['Vista al Jardín / Terraza'],
                'camas' => '1 Cama King Size',
                'imagenes' => $imagenesUrls,
                'serviciosIncluidos' => $serviciosIncluidos,
                'politicas' => $politicasData,
                'equipamiento' => $equipamiento,
            ],
            'similarRooms' => $this->resolverSimilares($habitacion),
        ];
    }

    private function resolverHabitacion(string $slug): ?Habitacion
    {
        $query = Habitacion::with([
            'categoria', 'ubicacion', 'detalle', 'imagenes', 'precios.moneda',
            'politicas', 'servicioAsignaciones.servicio', 'inventarioFijo.activo',
        ])->activas();

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            return $query->find((int) $matches[1]);
        }

        return $query->where('slug', $slug)->orWhere('codigo', $slug)->first();
    }

    /**
     * @return array<int, string>
     */
    private function resolverImagenes(Habitacion $habitacion): array
    {
        /** @var array<int, string> $urls */
        $urls = $habitacion->imagenes->map(function ($img): string {
            $url = trim((string) $img->url);

            return match (true) {
                str_starts_with($url, 'http://'), str_starts_with($url, 'https://'), str_starts_with($url, '/') => $url,
                default => '/storage/'.ltrim($url, '/'),
            };
        })->values()->toArray();

        if ($urls !== []) {
            return $urls;
        }

        return ['/images/group-room.jpg', '/images/main-room.jpg', '/images/room-detail.jpg', '/images/terrace.jpg'];
    }

    /**
     * @return array{adultos: int, ninos: int, total: int}
     */
    private function resolverCapacidades(Habitacion $habitacion): array
    {
        $detalle = $habitacion->detalle;
        $adultos = $detalle ? (int) $detalle->capacidad_adultos : 2;
        $ninos = $detalle ? (int) $detalle->capacidad_ninos : 0;

        return [
            'adultos' => $adultos,
            'ninos' => $ninos,
            'total' => $adultos + $ninos,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolverServicios(Habitacion $habitacion): array
    {
        /** @var array<int, string> $servicios */
        $servicios = $habitacion->servicioAsignaciones
            ->map(fn (ServicioAsignacion $sa) => $sa->servicio?->nombre)
            ->filter()
            ->values()
            ->toArray();

        if ($servicios !== []) {
            return $servicios;
        }

        return [
            'WiFi de Alta Velocidad (Fibra Óptica)',
            'Aire Acondicionado Inverter',
            'Televisión Smart HD 50"',
            'Baño Privado con Agua Caliente',
            'Cafetera Eléctrica con Selección de Café',
            'Caja de Seguridad Electrónica',
            'Acceso Ilimitado a la Piscina',
            'Desayuno Típico de Cortesía',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatearPoliticas(Habitacion $habitacion): array
    {
        /** @var array<int, array<string, mixed>> $politicas */
        $politicas = $habitacion->politicas->map(fn ($p): array => [
            'id' => $p->id,
            'nombre' => (string) ($p->nombre ?? ''),
            'descripcion' => $p->descripcion,
            'tipo' => 'Politica',
        ])->values()->toArray();

        if ($politicas !== []) {
            return $politicas;
        }

        return [
            ['nombre' => 'Horarios de Registro', 'descripcion' => 'Check-in a partir de las 14:00 horas. Check-out hasta las 12:00 horas del día siguiente.'],
            ['nombre' => 'Política de Cancelación', 'descripcion' => 'Cancelación gratuita realizando la solicitud al menos 48 horas antes de la fecha de ingreso.'],
            ['nombre' => 'Normativa de Humo & Mascotas', 'descripcion' => 'Habitación 100% libre de humo. No se admiten mascotas en las habitaciones.'],
        ];
    }

    /**
     * @return array<int, string|null>
     */
    private function resolverEquipamiento(Habitacion $habitacion): array
    {
        /** @var array<int, string|null> $equipamiento */
        $equipamiento = $habitacion->inventarioFijo
            ->map(fn (ActivoAsignacion $af) => $af->activo !== null && property_exists($af->activo, 'nombre') ? $af->activo->nombre : null)
            ->filter()
            ->values()
            ->all();

        return $equipamiento;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolverSimilares(Habitacion $habitacion): array
    {
        /** @var array<int, array<string, mixed>> $similares */
        $similares = Habitacion::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activas()
            ->where('id', '!=', $habitacion->id)
            ->take(3)
            ->get()
            ->map(function ($h): array {
                $p = $h->precios->first();
                $img = $h->imagenes->first();
                $imgUrl = $img
                    ? (str_starts_with($img->url, '/') ? $img->url : '/storage/'.ltrim($img->url, '/'))
                    : '/images/main-room.jpg';

                return [
                    'id' => $h->id,
                    'slug' => Str::slug($h->nombre ?? "Habitación $h->numero").'-'.$h->id,
                    'nombre' => $h->nombre ?? "Habitación $h->numero",
                    'categoria' => $h->categoria->nombre ?? 'Suite',
                    'precio' => $p ? (float) $p->precio : 45.0,
                    'moneda' => $p->moneda->simbolo ?? '$',
                    'imagen' => $imgUrl,
                ];
            })
            ->toArray();

        return $similares;
    }
}

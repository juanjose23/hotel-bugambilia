<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionItem;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Support\HotelInfo;

final class ObtenerDatosLanding
{
    private const string IMAGEN_HABITACION_FALLBACK = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';

    private const string IMAGEN_SERVICIO_FALLBACK = 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80';

    /**
     * @return array{
     *     hotelInfo: array<string, mixed>,
     *     habitaciones: array<int, array<string, mixed>>,
     *     servicios: array<int, array<string, mixed>>,
     *     promociones: array<int, array<string, mixed>>,
     *     categoriasHabitacion: array<int, string>
     * }
     */
    public function ejecutar(): array
    {
        return [
            'hotelInfo' => HotelInfo::getInfo(),
            'habitaciones' => $this->mapearHabitaciones(),
            'servicios' => $this->mapearServicios(),
            'promociones' => $this->mapearPromociones(),
            'categoriasHabitacion' => $this->obtenerCategoriasHabitacion(),
        ];
    }

    // -------------------------------------------------------------------------
    // Habitaciones
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearHabitaciones(): array
    {
        $habitaciones = Habitacion::with(['categoria', 'detalle', 'imagenes', 'precios.moneda'])
            ->activas()
            ->orderBy('id', 'asc')
            ->take(3)
            ->get();

        return $habitaciones->map(fn (Habitacion $h): array => $this->habitacionToArray($h))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function habitacionToArray(Habitacion $h): array
    {
        $precioObj = $h->precios->first();
        $monto = $precioObj ? (float) $precioObj->precio : 45.00;
        $moneda = $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$';
        $detalle = $h->detalle;
        $capacidad = $detalle ? (int) ($detalle->capacidad_adultos + $detalle->capacidad_ninos) : 2;
        $imagen = $h->imagenes->first();

        return [
            'id' => $h->id,
            'codigo' => $h->codigo,
            'numero' => $h->numero,
            'slug' => $h->slug,
            'nombre' => $h->nombre ?? "Habitación {$h->numero}",
            'descripcion' => $h->descripcion ?? 'Disfrute del máximo confort y elegancia en nuestras lujosas instalaciones en Estelí.',
            'categoria' => $h->categoria->nombre ?? 'Ejecutiva',
            'precio' => $monto,
            'precio_desde' => $monto,
            'moneda' => $moneda,
            'capacidad' => $capacidad,
            'disponibles' => 1,
            'total' => 1,
            'camas' => 'Matrimonial',
            'imagen' => $imagen->url ?? self::IMAGEN_HABITACION_FALLBACK,
        ];
    }

    // -------------------------------------------------------------------------
    // Servicios
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearServicios(): array
    {
        $servicios = Servicio::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activos()
            ->where('web', true)
            ->take(6)
            ->get();

        return $servicios->map(fn (Servicio $s): array => $this->servicioToArray($s))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function servicioToArray(Servicio $s): array
    {
        $precioObj = $s->precios->first();
        $imagen = $s->imagenes->first();

        return [
            'id' => $s->id,
            'codigo' => $s->codigo,
            'nombre' => $s->nombre,
            'descripcion' => $s->descripcion ?? 'Servicio exclusivo de alta calidad para nuestros huéspedes.',
            'categoria' => $s->categoria->nombre ?? 'Servicio General',
            'precio' => $precioObj ? (float) $precioObj->precio : null,
            'moneda' => $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$',
            'imagen' => $imagen->url ?? self::IMAGEN_SERVICIO_FALLBACK,
        ];
    }

    // -------------------------------------------------------------------------
    // Promociones
    // -------------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mapearPromociones(): array
    {
        $promociones = Promocion::with([
            'tipo',
            'imagenes',
            'items.item' => fn ($morphTo) => $morphTo->morphWith([
                Habitacion::class => [],
                Servicio::class => [],
                Espacio::class => [],
            ]),
            'precios.moneda',
        ])
            ->activos()
            ->where('web', true)
            ->orderBy('orden', 'asc')
            ->take(6)
            ->get();

        return $promociones->map(fn (Promocion $p): array => $this->promocionToArray($p))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function promocionToArray(Promocion $p): array
    {
        $precioObj = $p->precios->first();
        $montoBase = $p->precio_paquete
            ? (float) $p->precio_paquete
            : ($precioObj ? (float) $precioObj->precio : null);

        /** @var array<int, string> $itemsIncluidos */
        $itemsIncluidos = $p->items
            ->map(fn (PromocionItem $item): ?string => $this->nombreItemIncluido($item))
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $p->id,
            'codigo' => $p->codigo,
            'nombre' => $p->nombre,
            'descripcion' => $p->descripcion ?? 'Paquete promocional todo incluido en Hotel Bugambilias.',
            'badge' => $p->tipo->nombre ?? 'Paquete Especial',
            'precio_paquete' => $montoBase,
            'precio_final' => $p->precio_final ?? $montoBase,
            'descuento_porcentaje' => $p->descuento_porcentaje ? (float) $p->descuento_porcentaje : null,
            'descuento_monto' => $p->descuento_monto ? (float) $p->descuento_monto : null,
            'moneda' => $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$',
            'imagen' => $this->resolverUrlImagen($p->imagenes->first()),
            'itemsIncluidos' => $itemsIncluidos,
        ];
    }

    private function nombreItemIncluido(PromocionItem $item): ?string
    {
        $modelo = $item->item;

        if ($modelo === null) {
            return null;
        }

        $nombre = $modelo->getAttribute('nombre');

        return is_string($nombre) && $nombre !== '' ? $nombre : null;
    }

    private function resolverUrlImagen(?Imagen $imagenObj): ?string
    {
        if (! $imagenObj || empty($imagenObj->url)) {
            return null;
        }

        $url = trim((string) $imagenObj->url);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return '/storage/'.ltrim($url, '/');
    }

    // -------------------------------------------------------------------------
    // Catálogos
    // -------------------------------------------------------------------------

    /**
     * @return array<int, string>
     */
    private function obtenerCategoriasHabitacion(): array
    {
        /** @var array<int, string> $categorias */
        $categorias = Catalogo::whereHas('catalogoTipo', function ($query): void {
            $query->whereIn('codigo', ['CATEGORIA_HABITACION', 'categoria_habitacion']);
        })->pluck('nombre')->values()->all();

        return $categorias;
    }
}

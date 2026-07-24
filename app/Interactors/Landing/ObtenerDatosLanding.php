<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Servicios\Servicio;
use App\Support\HotelInfo;

final class ObtenerDatosLanding
{
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
        $hotelInfo = HotelInfo::getInfo();

        $habitacionesList = Habitacion::with(['categoria', 'detalle', 'imagenes', 'precios.moneda'])
            ->activas()
            ->orderBy('id', 'asc')
            ->take(3)
            ->get();

        $habitaciones = [];
        foreach ($habitacionesList as $h) {
            $precioObj = $h->precios->first();
            $monto = $precioObj ? (float) $precioObj->precio : 45.00;
            $moneda = $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$';

            $categoria = $h->categoria;
            $categoriaNombre = $categoria ? $categoria->nombre : 'Ejecutiva';

            $detalle = $h->detalle;
            $capacidad = $detalle ? (int) ($detalle->capacidad_adultos + $detalle->capacidad_ninos) : 2;

            $imagen = $h->imagenes->first();
            $imagenUrl = $imagen ? $imagen->url : 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';

            $habitaciones[] = [
                'id' => $h->id,
                'codigo' => $h->codigo,
                'numero' => $h->numero,
                'slug' => $h->slug,
                'nombre' => $h->nombre ?? "Habitación {$h->numero}",
                'descripcion' => $h->descripcion ?? 'Disfrute del máximo confort y elegancia en nuestras lujosas instalaciones en Estelí.',
                'categoria' => $categoriaNombre,
                'precio' => $monto,
                'precio_desde' => $monto,
                'moneda' => $moneda,
                'capacidad' => $capacidad,
                'disponibles' => 1,
                'total' => 1,
                'camas' => 'Matrimonial',
                'imagen' => $imagenUrl,
            ];
        }

        $serviciosList = Servicio::with(['categoria', 'imagenes', 'precios.moneda'])
            ->activos()
            ->where('web', true)
            ->take(6)
            ->get();

        $servicios = [];
        foreach ($serviciosList as $s) {
            $precioObj = $s->precios->first();
            $monto = $precioObj ? (float) $precioObj->precio : null;
            $moneda = $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$';

            $categoria = $s->categoria;
            $categoriaNombre = $categoria ? $categoria->nombre : 'Servicio General';

            $imagen = $s->imagenes->first();
            $imagenUrl = $imagen ? $imagen->url : 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80';

            $servicios[] = [
                'id' => $s->id,
                'codigo' => $s->codigo,
                'nombre' => $s->nombre,
                'descripcion' => $s->descripcion ?? 'Servicio exclusivo de alta calidad para nuestros huéspedes.',
                'categoria' => $categoriaNombre,
                'precio' => $monto,
                'moneda' => $moneda,
                'imagen' => $imagenUrl,
            ];
        }

        $promocionesList = Promocion::with(['tipo', 'imagenes', 'items.item', 'precios.moneda'])
            ->activos()
            ->where('web', true)
            ->orderBy('orden', 'asc')
            ->take(6)
            ->get();

        $promociones = [];
        foreach ($promocionesList as $p) {
            $imagenObj = $p->imagenes->first();
            $imagenUrl = null;
            if ($imagenObj && ! empty($imagenObj->url)) {
                $url = trim((string) $imagenObj->url);
                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
                    $imagenUrl = $url;
                } else {
                    $imagenUrl = '/storage/'.ltrim($url, '/');
                }
            }

            $precioObj = $p->precios->first();
            $montoBase = $p->precio_paquete ? (float) $p->precio_paquete : ($precioObj ? (float) $precioObj->precio : null);
            $simboloMoneda = $precioObj && $precioObj->moneda ? $precioObj->moneda->simbolo : '$';

            $itemsIncluidos = $p->items->map(function ($item) {
                if (! $item->item) {
                    return null;
                }

                return $item->item->nombre ?? null;
            })->filter()->values()->all();

            $promociones[] = [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion ?? 'Paquete promocional todo incluido en Hotel Bugambilias.',
                'badge' => $p->tipo ? $p->tipo->nombre : 'Paquete Especial',
                'precio_paquete' => $montoBase,
                'precio_final' => $p->precio_final ?? $montoBase,
                'descuento_porcentaje' => $p->descuento_porcentaje ? (float) $p->descuento_porcentaje : null,
                'descuento_monto' => $p->descuento_monto ? (float) $p->descuento_monto : null,
                'moneda' => $simboloMoneda,
                'imagen' => $imagenUrl,
                'itemsIncluidos' => $itemsIncluidos,
            ];
        }

        /** @var array<int, string> $categoriasHabitacion */
        $categoriasHabitacion = Catalogo::whereHas('catalogoTipo', function ($query) {
            $query->whereIn('codigo', ['CATEGORIA_HABITACION', 'categoria_habitacion']);
        })->pluck('nombre')->values()->all();

        return [
            'hotelInfo' => $hotelInfo,
            'habitaciones' => $habitaciones,
            'servicios' => $servicios,
            'promociones' => $promociones,
            'categoriasHabitacion' => $categoriasHabitacion,
        ];
    }
}

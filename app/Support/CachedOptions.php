<?php

declare(strict_types=1);

namespace App\Support;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Proveedor;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Queries\Limpieza\Ubicacion\ObtenerPathUbicacion;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class CachedOptions
{
    public function __construct(
        private ObtenerNombrePersona $obtenerNombrePersona,
    ) {}

    /**
     * @return Collection<int, string>
     */
    public static function productos(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:productos', 3600, fn () => Producto::query()->orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function productosKit(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:productos_kit', 3600, fn () => Producto::query()
            ->whereHas('kitItems')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function catalogos(string $codigoTipo): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember("cached_options:catalogos:$codigoTipo", 3600, fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', $codigoTipo))
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public function proveedores(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:proveedores', 3600, fn () => Proveedor::with(['persona.personaNatural', 'persona.personaJuridica'])
            ->get()
            ->mapWithKeys(fn ($prov) => [
                $prov->id => $prov->persona ? $this->obtenerNombrePersona->ejecutar($prov->persona) : "Proveedor #$prov->id",
            ])
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function ubicacionesAlmacen(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:ubicaciones_almacen', 3600, fn () => Ubicacion::whereIn('tipo', ['almacen', 'bodega', 'carrito'])
            ->where('estado', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function monedas(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:monedas', 7200, fn () => Moneda::orderBy('codigo')->pluck('codigo', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function ubicaciones(): Collection
    {
        /** @var array<int|string, string> $data */
        $data = Cache::remember(
            'catalogos.ubicaciones',
            60 * 60 * 6,
            fn () => app(ObtenerPathUbicacion::class)->ejecutar()
        );

        return collect($data);
    }

    /**
     * @param  list<string>  $codigos
     * @return Collection<int, string>
     */
    public static function catalogosPorVarios(array $codigos): Collection
    {
        sort($codigos);
        $cacheKey = 'cached_options:catalogos:'.implode('_', $codigos);

        /** @var array<int, string> $data */
        $data = Cache::remember($cacheKey, 3600, fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->whereIn('codigo', $codigos))
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function servicios(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:servicios', 3600, fn () => Servicio::orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function serviciosActivos(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:servicios_activos', 3600, fn () => Servicio::activos()->orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function habitaciones(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:habitaciones', 3600, fn () => Habitacion::orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int, string>
     */
    public static function espacios(): Collection
    {
        /** @var array<int, string> $data */
        $data = Cache::remember('cached_options:espacios', 3600, fn () => Espacio::orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    public static function clear(): void
    {
        Cache::forget('cached_options:productos');
        Cache::forget('cached_options:productos_kit');
        Cache::forget('cached_options:proveedores');
        Cache::forget('cached_options:ubicaciones_almacen');
        Cache::forget('cached_options:monedas');
        Cache::forget('cached_options:ubicaciones_activas');
        Cache::forget('cached_options:servicios');
        Cache::forget('cached_options:servicios_activos');
        Cache::forget('cached_options:habitaciones');
        Cache::forget('cached_options:espacios');
        Cache::forget('lote_table:sub_ubicaciones_all');
    }
}

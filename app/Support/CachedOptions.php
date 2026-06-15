<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\Proveedor;
use App\Models\Monedas\Moneda;
use App\UseCases\Shared\Queries\ObtenerNombrePersona;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class CachedOptions
{
    /**
     * @return Collection<int|string, string>
     */
    public static function productos(): Collection
    {
        $data = Cache::remember('cached_options:productos', 3600, fn () => Producto::query()->orderBy('nombre')->pluck('nombre', 'id')->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function catalogos(string $codigoTipo): Collection
    {
        $data = Cache::remember("cached_options:catalogos:{$codigoTipo}", 3600, fn () => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', $codigoTipo))
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function proveedores(): Collection
    {
        $data = Cache::remember('cached_options:proveedores', 3600, fn () => Proveedor::with(['persona.personaNatural', 'persona.personaJuridica'])
            ->get()
            ->mapWithKeys(fn ($prov) => [
                $prov->id => $prov->persona ? app(ObtenerNombrePersona::class)->ejecutar($prov->persona) : "Proveedor #{$prov->id}",
            ])
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function ubicacionesAlmacen(): Collection
    {
        $data = Cache::remember('cached_options:ubicaciones_almacen', 3600, fn () => Ubicacion::where('tipo', 'almacen')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray()
        );

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function productosKit(): Collection
    {
        $data = Cache::remember('cached_options:productos_kit', 3600, function () {
            $ids = Producto::whereIn('id', function ($q) {
                $q->select('producto_padre_id')->from('producto_kit');
            })->pluck('nombre', 'id')->toArray();

            return $ids;
        });

        return collect($data);
    }

    /**
     * @return Collection<int|string, string>
     */
    public static function monedas(): Collection
    {
        $data = Cache::remember('cached_options:monedas', 7200, fn () => Moneda::orderBy('nombre')->pluck('nombre', 'id')->toArray()
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
    }
}

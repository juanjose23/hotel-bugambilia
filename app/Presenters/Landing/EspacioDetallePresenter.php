<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Actions\Landing\ResolverUrlImagen;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Str;

final class EspacioDetallePresenter
{
    public function __construct(
        private readonly ResolverUrlImagen $resolverUrlImagen,
        private readonly ServicioAsignacionPresenter $serviciosPresenter,
        private readonly PoliticaPresenter $politicasPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function detalle(Espacio $espacio): array
    {
        return [
            'id' => $espacio->id,
            'codigo' => $espacio->codigo,
            'slug' => $espacio->slug ?? Str::slug($espacio->nombre),
            'nombre' => $espacio->nombre,
            'tipo' => $espacio->tipo->value,
            'tipo_label' => $espacio->tipo->getLabel(),
            'descripcion' => ! empty($espacio->descripcion) ? $espacio->descripcion : 'Sin descripción detallada disponible en este momento.',
            'ubicacion' => $espacio->ubicacion->nombre ?? 'Instalaciones Principales',
            ...$this->precios($espacio),
            'capacidad' => $espacio->capacidad_personas ?? 1,
            'web' => (bool) $espacio->web,
            'reservable' => (bool) $espacio->reservable,
            'es_restaurante' => $espacio->tipo->value === 'restaurante',
            'imagenes' => $this->resolverUrlImagen->deEspacio($espacio),
            'meta_datos' => $espacio->meta_datos ?? [],
            'serviciosIncluidos' => $this->serviciosPresenter->lista($espacio->servicioAsignaciones),
            'politicas' => $this->politicasPresenter->lista($espacio->politicas),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function similares(Espacio $espacio): array
    {
        return Espacio::with(['precios.moneda', 'imagenes'])
            ->activosWeb()
            ->whereNull('padre_id')
            ->where('id', '!=', $espacio->id)
            ->take(3)
            ->get()
            ->map(fn (Espacio $e): array => $this->similar($e))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function precios(Espacio $espacio): array
    {
        $preciosOrdenados = $espacio->precios->sortByDesc('es_oferta');
        $precioHoraObj = $preciosOrdenados->first(fn ($p) => $p->tipo_precio->value === 'por_hora');
        $precioBaseObj = $preciosOrdenados->first(fn ($p) => $p->tipo_precio->value === 'base');
        $precioObj = $precioHoraObj ?? $precioBaseObj ?? $preciosOrdenados->first();

        $precioPorHora = $precioHoraObj ? (float) $precioHoraObj->precio : 0.0;
        $precioBase = $precioBaseObj ? (float) $precioBaseObj->precio : 0.0;

        return [
            'precio' => $precioPorHora > 0 ? $precioPorHora : $precioBase,
            'precio_por_hora' => $precioPorHora,
            'precio_base' => $precioBase,
            'es_oferta' => $precioObj !== null ? (bool) $precioObj->es_oferta : false,
            'tipo_tarifa_label' => $precioPorHora > 0 ? '/ hora' : ($precioBase > 0 ? '/ evento' : ''),
            'moneda' => $precioObj->moneda->simbolo ?? 'C$',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function similar(Espacio $e): array
    {
        $p = $e->precios->first();
        $imgUrl = $e->imagenes->first() !== null
            ? $this->resolverUrlImagen->ejecutar($e->imagenes->first()->url) ?? '/images/terrace.jpg'
            : '/images/terrace.jpg';

        return [
            'id' => $e->id,
            'slug' => $e->slug ?? Str::slug($e->nombre),
            'nombre' => $e->nombre,
            'tipo' => $e->tipo->value,
            'precio' => $p ? (float) $p->precio : 0.0,
            'moneda' => $p->moneda->simbolo ?? 'C$',
            'imagen' => $imgUrl,
        ];
    }
}

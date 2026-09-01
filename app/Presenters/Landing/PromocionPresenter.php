<?php

declare(strict_types=1);

namespace App\Presenters\Landing;

use App\Actions\Landing\ResolverUrlImagen;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PromocionPresenter
{
    public function __construct(
        private readonly ResolverUrlImagen $resolverUrlImagen,
    ) {}

    /**
     * @param  Collection<int, Promocion>  $promociones
     * @return array<int, array<string, mixed>>
     */
    public function coleccion(Collection $promociones): array
    {
        return $promociones->map(fn (Promocion $p): array => $this->presentar($p))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function presentar(Promocion $p): array
    {
        $precioOriginal = $p->precio_paquete !== null ? (float) $p->precio_paquete : null;
        if ($precioOriginal === null) {
            $precioObj = $p->precios->first();
            if ($precioObj !== null && $precioObj->precio > 0) {
                $precioOriginal = (float) $precioObj->precio;
            }
        }

        $precioFinal = $p->precio_final ?? $precioOriginal ?? 0.0;
        $primerPrecio = $p->precios->first();
        $moneda = $primerPrecio !== null ? ($primerPrecio->moneda->simbolo ?? '$') : '$';

        $descuentoPorcentaje = $p->descuento_porcentaje !== null ? (float) $p->descuento_porcentaje : null;
        $descuentoMonto = $p->descuento_monto !== null ? (float) $p->descuento_monto : null;

        // Calcular porcentaje implícito si solo hay descuento en monto
        if ($descuentoPorcentaje === null && $descuentoMonto !== null && $precioOriginal !== null && $precioOriginal > 0) {
            $descuentoPorcentaje = round(($descuentoMonto / $precioOriginal) * 100, 0);
        }

        $imagenes = $p->imagenes->map(function ($img): ?string {
            return $this->resolverUrlImagen->ejecutar($img->url);
        })->filter()->values()->all();

        if (empty($imagenes)) {
            $imagenes = ['/images/hero-main.webp'];
        }

        $beneficios = $p->beneficios->map(function (PromocionBeneficio $b): array {
            return [
                'id' => $b->id,
                'titulo' => $b->tipo->getLabel(),
                'descripcion' => (string) ($b->descripcion ?? ''),
                'tipo' => $b->tipo->value,
                'valor' => $b->valor !== null ? (float) $b->valor : null,
            ];
        })->values()->all();

        $items = $p->items->map(function (PromocionItem $item): array {
            return [
                'id' => $item->id,
                'tipo' => $item->item_type ? class_basename($item->item_type) : 'Servicio',
                'precio_especial' => $item->precio_especial ? (float) $item->precio_especial : null,
                'incluido' => (bool) ($item->incluido ?? true),
            ];
        })->values()->all();

        $tipo = $p->tipo ? $p->tipo->nombre : 'Promoción Especial';

        return [
            'id' => $p->id,
            'codigo' => $p->codigo ?? "PROMO-{$p->id}",
            'slug' => $p->slug ?? Str::slug($p->nombre ?? "promocion-{$p->id}"),
            'nombre' => $p->nombre ?? 'Paquete Exclusivo Bugambilias',
            'descripcion' => $p->descripcion ?? 'Aprovecha nuestras tarifas especiales con beneficios exclusivos para una estancia inolvidable.',
            'tipo' => $tipo,
            'precio_original' => $precioOriginal,
            'precio_final' => $precioFinal,
            'descuento_porcentaje' => $descuentoPorcentaje,
            'descuento_monto' => $descuentoMonto,
            'moneda' => $moneda,
            'fecha_inicio' => $p->fecha_inicio->format('Y-m-d'),
            'fecha_fin' => $p->fecha_fin->format('Y-m-d'),
            'imagen' => $imagenes[0],
            'imagenes' => $imagenes,
            'beneficios' => $beneficios,
            'items' => $items,
        ];
    }
}

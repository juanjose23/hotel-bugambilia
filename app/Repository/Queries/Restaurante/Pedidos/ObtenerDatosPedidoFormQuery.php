<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Precio;
use Illuminate\Support\Collection;

final class ObtenerDatosPedidoFormQuery
{
    /**
     * @return array<int|string, string>
     */
    public function mesasDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Espacio::where('tipo', TipoEspacio::MESA)
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function platosActivosAgrupadosPorCategoria(): array
    {
        /** @var array<string, array<int|string, string>> */
        return Plato::query()
            ->where('estado', 1)
            ->with('categoria')
            ->get()
            ->groupBy(fn (Plato $plato): string => $plato->categoria->nombre ?? 'Menú General / Varios')
            ->map(fn (Collection $grupo): array => $grupo->pluck('nombre', 'id')->all())
            ->all();
    }

    /**
     * @param  array<int, int>  $platoIds
     * @return Collection<int, Plato>
     */
    public function platosParaPreorden(array $platoIds): Collection
    {
        if ($platoIds === []) {
            return collect();
        }

        /** @var Collection<int, Plato> $platos */
        $platos = Plato::query()
            ->whereIn('id', $platoIds)
            ->get()
            ->keyBy('id');

        return $platos;
    }

    /**
     * Obtiene el precio actual del plato, prefiriendo la moneda por defecto del sistema.
     */
    public function precioActualDePlato(int $platoId): ?float
    {
        /** @var Plato|null $plato */
        $plato = Plato::query()->find($platoId);

        if (! $plato instanceof Plato) {
            return null;
        }

        $monedaDefault = Moneda::query()->where('es_predeterminada', true)->value('id');

        if (is_numeric($monedaDefault)) {
            $precioDefault = $plato->precios()
                ->where('moneda_id', (int) $monedaDefault)
                ->latest()
                ->first();

            if ($precioDefault !== null) {
                return (float) $precioDefault->precio;
            }
        }

        $precio = $plato->precios()->latest()->first();

        return $precio !== null ? (float) $precio->precio : null;
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    public function productosVendiblesAgrupadosPorCategoria(): array
    {
        /** @var array<string, array<int|string, string>> */
        return Producto::query()
            ->where('estado', EstadoGeneral::Activo->value)
            ->with('categoria')
            ->get()
            ->groupBy(fn (Producto $p): string => $p->categoria->nombre ?? 'Productos Generales')
            ->map(fn (Collection $grupo): array => $grupo->pluck('nombre', 'id')->all())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function variantesDeProducto(int $productoId): array
    {
        /** @var array<int, string> */
        return ProductoVariante::query()
            ->where('producto_id', $productoId)
            ->pluck('nombre_variante', 'id')
            ->all();
    }

    public function productoTieneVariantes(int $productoId): bool
    {
        return ProductoVariante::query()
            ->where('producto_id', $productoId)
            ->exists();
    }

    /**
     * Obtiene el precio de venta actual de un producto o variante desde la tabla precios.
     */
    public function precioActualDeProducto(int $productoId, ?int $varianteId = null): ?float
    {
        $monedaDefault = Moneda::query()->where('es_predeterminada', true)->value('id');

        if ($varianteId !== null) {
            $precioQuery = Precio::query()
                ->where('priceable_type', ProductoVariante::class)
                ->where('priceable_id', $varianteId);

            if (is_numeric($monedaDefault)) {
                $precioMoneda = (clone $precioQuery)->where('moneda_id', (int) $monedaDefault)->latest()->first();
                if ($precioMoneda !== null) {
                    return (float) $precioMoneda->precio;
                }
            }

            $precioVar = $precioQuery->latest()->first();
            if ($precioVar !== null) {
                return (float) $precioVar->precio;
            }
        }

        $precioProdQuery = Precio::query()
            ->where('priceable_type', Producto::class)
            ->where('priceable_id', $productoId);

        if (is_numeric($monedaDefault)) {
            $precioMoneda = (clone $precioProdQuery)->where('moneda_id', (int) $monedaDefault)->latest()->first();
            if ($precioMoneda !== null) {
                return (float) $precioMoneda->precio;
            }
        }

        $precioProd = $precioProdQuery->latest()->first();

        return $precioProd !== null ? (float) $precioProd->precio : null;
    }
}

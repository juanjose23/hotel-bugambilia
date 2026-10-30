<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Stock;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;

final class ObtenerStockDisponibleRestauranteQuery
{
    public function __construct(private readonly ExistenciasCocinaRepositorio $existencias) {}

    /**
     * Consulta el stock disponible mediante la relación polimórfica stockable
     * contemplando productos simples (sin variante) y productos con variantes.
     *
     * @return array{disponible: float, tiene_stock: bool, punto_venta: string}
     */
    public function ejecutar(int $productoId, ?int $varianteId = null, ?int $mesaId = null): array
    {
        $espacio = $mesaId !== null ? Espacio::query()->with('padre')->find($mesaId) : null;
        $stockTotal = (float) $this->existencias->producto($productoId, $varianteId, $mesaId)->sum('cantidad_actual');

        $puntoVenta = $espacio instanceof Espacio
            ? ($espacio->padre instanceof Espacio ? $espacio->padre->nombre : $espacio->nombre)
            : 'Restaurante / Bar General';

        return [
            'disponible' => $stockTotal,
            'tiene_stock' => $stockTotal > 0,
            'punto_venta' => $puntoVenta,
        ];
    }

    /**
     * Retorna el precio base de venta para un producto o variante.
     */
    public function obtenerPrecioVenta(int $productoId, ?int $varianteId = null): float
    {
        $monedaDefault = Moneda::query()->where('es_predeterminada', true)->value('id');

        if ($varianteId !== null && $varianteId > 0) {
            $queryVar = Precio::query()
                ->where('priceable_type', ProductoVariante::class)
                ->where('priceable_id', $varianteId)
                ->where('estado', EstadoGeneral::Activo);

            if (is_numeric($monedaDefault)) {
                $precioMoneda = (clone $queryVar)->where('moneda_id', (int) $monedaDefault)->value('precio');
                if (is_numeric($precioMoneda)) {
                    return (float) $precioMoneda;
                }
            }

            $precioVariante = $queryVar->value('precio');
            if (is_numeric($precioVariante)) {
                return (float) $precioVariante;
            }
        }

        $queryProd = Precio::query()
            ->where('priceable_type', Producto::class)
            ->where('priceable_id', $productoId)
            ->where('estado', EstadoGeneral::Activo);

        if (is_numeric($monedaDefault)) {
            $precioMoneda = (clone $queryProd)->where('moneda_id', (int) $monedaDefault)->value('precio');
            if (is_numeric($precioMoneda)) {
                return (float) $precioMoneda;
            }
        }

        $precioProducto = $queryProd->value('precio');
        if (is_numeric($precioProducto)) {
            return (float) $precioProducto;
        }

        return 0.00;
    }
}

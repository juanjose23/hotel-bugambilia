<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Persistencia\Restaurante\ExistenciasCocinaRepositorio;
use Illuminate\Support\Collection;

final readonly class ObtenerIngredientesPedidoQuery
{
    public function __construct(private ExistenciasCocinaRepositorio $existencias) {}

    /** @return array{ubicacion_id: int, ingredientes: Collection<int, ProductoKit>}|null */
    public function ejecutar(PedidoItem $item): ?array
    {
        $item->loadMissing(['plato.receta', 'pedido']);
        $productoId = $item->plato->receta->id ?? null;
        if (! is_int($productoId)) {
            return null;
        }
        $cocinaId = $this->existencias->ubicacion($item->area_cocina ?? $item->plato?->area_cocina, $item->pedido?->mesa_id !== null ? (int) $item->pedido->mesa_id : null);
        $ingredientes = ProductoKit::query()->with('variante.producto')->where('producto_padre_id', $productoId)->get();

        return ['ubicacion_id' => $cocinaId, 'ingredientes' => $ingredientes];
    }
}

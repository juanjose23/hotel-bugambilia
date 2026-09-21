<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Carrito;

use App\BusinessLogic\Inventario\Servicios\ReabastecedorFefo;
use Illuminate\Support\Facades\DB;

final readonly class ReabastecerCarrito
{
    public function __construct(
        private ReabastecedorFefo $reabastecedorFefo,
    ) {}

    /**
     * Reabastece un carrito desde una bodega/almacén de origen.
     *
     * @param  array<int, array{producto_id?: int|null, producto_variante_id?: int|null, cantidad: float, lote_id?: int|null}>  $items
     */
    public function execute(int $bodegaOrigenId, int $carritoDestinoId, array $items, ?int $creadoPorId = null): void
    {
        DB::transaction(fn () => $this->reabastecedorFefo->reabastecer($bodegaOrigenId, $carritoDestinoId, $items, $creadoPorId));
    }
}

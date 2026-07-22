<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Stock;
use Illuminate\Support\Collection;

final class ObtenerStockParaConsumo
{
    /**
     * @return Collection<int, Stock>
     */
    public function ejecutar(int $productoId, int $ubicacionId, ?int $productoVarianteId = null): Collection
    {
        return Stock::query()
            ->with(['lote'])
            ->where('producto_id', $productoId)
            ->where('ubicacion_id', $ubicacionId)
            ->where('cantidad', '>', 0)
            ->where(function ($q) {
                $q->whereNull('lote_id')
                    ->orWhereHas('lote', function ($sub) {
                        $sub->where('estado', EstadoLote::Disponible)
                            ->where(function ($dateQuery) {
                                $dateQuery->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            });
                    });
            })
            ->when($productoVarianteId !== null, function ($q) use ($productoVarianteId) {
                $q->where('producto_variante_id', $productoVarianteId);
            })
            ->get();
    }
}

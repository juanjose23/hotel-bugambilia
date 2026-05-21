<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Alertas;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Collection;

/**
 * HTB-INV-012 — Lotes Vencidos
 * Lotes cuya fecha de vencimiento ha expirado o están marcados como vencidos.
 */
class ObtenerLotesVencidos
{
    /**
     * @param  array{producto_id?: int|null}  $filtros
     * @return Collection<int, Lote>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return Lote::with(['producto', 'variante', 'ubicacion'])
            ->where(function ($query) {
                $query->where('estado', EstadoLote::Vencido)
                    ->orWhere(function ($q) {
                        $q->whereNotNull('fecha_vencimiento')
                            ->where('fecha_vencimiento', '<=', now()->toDateString())
                            ->where('cantidad_disponible', '>', 0);
                    });
            })
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->orderBy('fecha_vencimiento', 'desc')
            ->get();
    }
}

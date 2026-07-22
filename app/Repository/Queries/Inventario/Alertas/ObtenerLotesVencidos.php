<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Alertas;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
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
        return Lote::query()
            ->with([
                'producto:id,nombre',
                'variante:id,producto_id,codigo,nombre_variante',
                'ubicacion:id,nombre',
            ])
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

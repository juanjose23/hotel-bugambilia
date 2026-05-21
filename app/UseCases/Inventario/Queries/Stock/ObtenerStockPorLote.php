<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Stock;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * HTB-INV-002 — Stock Actual por Lote
 * Retorna cada lote con su producto, cantidad, ubicación, vencimiento y días restantes.
 */
class ObtenerStockPorLote
{
    /**
     * @param  array{estado?: EstadoLote|null, producto_id?: int|null, ubicacion_id?: int|null, solo_proximos?: bool}  $filtros
     * @return LengthAwarePaginator<int, Lote>
     */
    public function ejecutar(array $filtros = [], int $perPage = 25): LengthAwarePaginator
    {
        return Lote::with(['producto', 'variante', 'ubicacion'])
            ->when(
                isset($filtros['estado']),
                fn ($q) => $q->where('estado', $filtros['estado'])
            )
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->when(
                isset($filtros['ubicacion_id']) && $filtros['ubicacion_id'],
                fn ($q) => $q->where('ubicacion_id', $filtros['ubicacion_id'])
            )
            ->when(
                isset($filtros['solo_proximos']) && $filtros['solo_proximos'],
                fn ($q) => $q
                    ->whereNotNull('fecha_vencimiento')
                    ->where('fecha_vencimiento', '>', now()->toDateString())
                    ->where('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            )
            ->orderBy('fecha_vencimiento')
            ->paginate($perPage);
    }
}

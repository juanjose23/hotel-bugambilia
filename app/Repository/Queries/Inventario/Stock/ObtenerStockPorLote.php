<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
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
        return Lote::query()
            ->with([
                'producto:id,nombre',
                'variante:id,producto_id,codigo,nombre_variante',
                'ubicacion:id,nombre',
            ])
            ->when(
                $filtros['estado'] ?? null,
                fn ($q, $estado) => $q->where('estado', $estado)
            )
            ->when(
                $filtros['producto_id'] ?? null,
                fn ($q, $productoId) => $q->where('producto_id', $productoId)
            )
            ->when(
                $filtros['ubicacion_id'] ?? null,
                fn ($q, $ubicacionId) => $q->where('ubicacion_id', $ubicacionId)
            )
            ->when(
                ! empty($filtros['solo_proximos']),
                fn ($q) => $q
                    ->whereNotNull('fecha_vencimiento')
                    ->where('fecha_vencimiento', '>', now()->toDateString())
                    ->where('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
            )
            ->orderBy('fecha_vencimiento')
            ->paginate($perPage);
    }
}

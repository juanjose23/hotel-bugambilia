<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Alertas;

use App\Enums\Inventario\EstadoLote;
use App\Models\Inventario\Lote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * HTB-INV-004 — Lotes en Cuarentena
 * Lista lotes que aún no han sido liberados ni rechazados, con tiempo de retención.
 */
class ObtenerLotesCuarentena
{
    /**
     * @param  array{producto_id?: int|null, fecha_desde?: Carbon|string|null}  $filtros
     * @return Collection<int, Lote>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return Lote::with(['producto', 'variante', 'ubicacion'])
            ->where('estado', EstadoLote::Cuarentena)
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->when(
                isset($filtros['fecha_desde']) && $filtros['fecha_desde'],
                fn ($q) => $q->where('updated_at', '>=', Carbon::parse($filtros['fecha_desde'])->startOfDay())
            )
            ->orderBy('updated_at')
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Alertas;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
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
        return Lote::query()
            ->with([
                'producto:id,nombre',
                'variante:id,producto_id,codigo,nombre_variante',
                'ubicacion:id,nombre',
            ])
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

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Alertas;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Database\Eloquent\Collection;

/**
 * HTB-INV-005 — Lotes Próximos a Vencer
 * Lotes con fecha de vencimiento dentro de los próximos X días.
 */
class ObtenerLotesProximosVencer
{
    /**
     * @param  array{dias?: int, producto_id?: int|null}  $filtros
     * @return Collection<int, Lote>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        $dias = isset($filtros['dias']) && $filtros['dias'] > 0 ? (int) $filtros['dias'] : 30;

        return Lote::query()
            ->with([
                'producto:id,nombre',
                'variante:id,producto_id,codigo,nombre_variante',
                'ubicacion:id,nombre',
            ])
            ->whereIn('estado', [EstadoLote::Disponible, EstadoLote::Cuarentena])
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '>', now()->toDateString())
            ->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString())
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->orderBy('fecha_vencimiento')
            ->get();
    }
}

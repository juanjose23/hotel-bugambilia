<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Mermas;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * HTB-INV-006 — Lotes Vencidos / Mermas
 * Lotes ya caducados o rechazados con detalle de cantidad y ubicación de merma.
 */
class ObtenerLotesMerma
{
    /**
     * @param  array{periodo_desde?: Carbon|string|null, periodo_hasta?: Carbon|string|null, motivo?: string|null}  $filtros
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
            ->whereIn('estado', [EstadoLote::Vencido, EstadoLote::Rechazado])
            ->when(
                isset($filtros['periodo_desde']) && $filtros['periodo_desde'],
                fn ($q) => $q->where('updated_at', '>=', Carbon::parse($filtros['periodo_desde'])->startOfDay())
            )
            ->when(
                isset($filtros['periodo_hasta']) && $filtros['periodo_hasta'],
                fn ($q) => $q->where('updated_at', '<=', Carbon::parse($filtros['periodo_hasta'])->endOfDay())
            )
            ->when(
                isset($filtros['motivo']) && $filtros['motivo'] === 'caducidad',
                fn ($q) => $q->where('estado', EstadoLote::Vencido)
            )
            ->when(
                isset($filtros['motivo']) && $filtros['motivo'] === 'calidad',
                fn ($q) => $q->where('estado', EstadoLote::Rechazado)
            )
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}

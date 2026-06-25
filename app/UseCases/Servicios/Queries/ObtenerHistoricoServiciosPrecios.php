<?php

declare(strict_types=1);

namespace App\UseCases\Servicios\Queries;

use App\Models\Servicios\Servicio;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * HTB-SER-001 — Histórico de Servicios por Precio por Moneda
 * Retorna el historial de precios de servicios agrupado por moneda,
 * incluyendo fechas de vigencia y estado actual.
 */
class ObtenerHistoricoServiciosPrecios
{
    /**
     * @param  array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null}  $filtros
     * @return Collection<int, stdClass>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        return DB::table('precios as p')
            ->join('servicios as s', function ($join) {
                $join->on('p.priceable_id', '=', 's.id')
                    ->where('p.priceable_type', '=', Servicio::class);
            })
            ->join('monedas as m', 'p.moneda_id', '=', 'm.id')
            ->leftJoin('catalogos as c', 's.categoria_id', '=', 'c.id')
            ->whereNull('p.deleted_at')
            ->when(
                isset($filtros['servicio_id']) && $filtros['servicio_id'],
                fn ($q) => $q->where('p.priceable_id', $filtros['servicio_id'])
            )
            ->when(
                isset($filtros['moneda_id']) && $filtros['moneda_id'],
                fn ($q) => $q->where('p.moneda_id', $filtros['moneda_id'])
            )
            ->when(
                isset($filtros['categoria_id']) && $filtros['categoria_id'],
                fn ($q) => $q->where('s.categoria_id', $filtros['categoria_id'])
            )
            ->when(
                ! empty($filtros['estado']),
                fn ($q) => $q->where('p.estado', (int) $filtros['estado'])
            )
            ->select(
                'p.id',
                's.id as servicio_id',
                's.nombre as servicio',
                's.codigo as servicio_codigo',
                'c.id as categoria_id',
                'c.nombre as categoria',
                'm.id as moneda_id',
                'm.nombre as moneda',
                'm.simbolo as moneda_simbolo',
                'm.codigo as moneda_codigo',
                'p.precio',
                'p.fecha_inicio',
                'p.fecha_fin',
                'p.estado',
                'p.es_oferta',
                'p.created_at',
            )
            ->orderBy('c.nombre')
            ->orderBy('m.nombre')
            ->orderBy('s.nombre')
            ->orderByDesc('p.fecha_inicio')
            ->get();
    }

    /**
     * @param  array{servicio_id?: int|null, moneda_id?: int|null, estado?: int|null, categoria_id?: int|null}  $filtros
     * @return Collection<string, Collection<int, stdClass>>
     */
    public function agrupadoPorCategoria(array $filtros = []): Collection
    {
        $data = $this->ejecutar($filtros);

        return $data->groupBy(fn ($item): string => $item->categoria ?? 'Sin categoría')
            ->sortKeys();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\Repository\Models\Inventario\MovimientoStock;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ObtenerAjustesInventario
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return LengthAwarePaginator<int, MovimientoStock>
     */
    public function ejecutar(array $filtros = [], int $perPage = 100): LengthAwarePaginator
    {
        $fechaDesde = isset($filtros['fecha_desde']) && is_string($filtros['fecha_desde'])
            ? $filtros['fecha_desde']
            : null;
        $fechaHasta = isset($filtros['fecha_hasta']) && is_string($filtros['fecha_hasta'])
            ? $filtros['fecha_hasta']
            : null;

        return MovimientoStock::query()
            ->with([
                'lote:id,codigo_lote,costo_unitario',
                'producto:id,nombre',
                'ubicacionOrigen:id,nombre',
                'ubicacionDestino:id,nombre',
                'creadoPor:id,name',
                'creadoPor.persona:id,colaborador_id,primer_nombre',
            ])
            ->where('tipo', 'MOV_AJUSTE')
            ->when(
                isset($filtros['producto_id']) && $filtros['producto_id'],
                fn ($q) => $q->where('producto_id', $filtros['producto_id'])
            )
            ->when(
                $fechaDesde !== null,
                fn ($q) => $q->where('created_at', '>=', Carbon::parse($fechaDesde)->startOfDay())
            )
            ->when(
                $fechaHasta !== null,
                fn ($q) => $q->where('created_at', '<=', Carbon::parse($fechaHasta)->endOfDay())
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

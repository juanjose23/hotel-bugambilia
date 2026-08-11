<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\Repository\Models\Inventario\MovimientoStock;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ObtenerAjustesInventario
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return LengthAwarePaginator<int, MovimientoStock>
     */
    public function ejecutar(array $filtros = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->aplicarFiltros($this->consultaBase(), $filtros)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @return Builder<MovimientoStock>
     */
    private function consultaBase(): Builder
    {
        return MovimientoStock::query()
            ->with([
                'lote:id,codigo_lote,costo_unitario',
                'producto:id,nombre',
                'ubicacionOrigen:id,nombre',
                'ubicacionDestino:id,nombre',
                'creadoPor:id,name',
                'creadoPor.persona:id,colaborador_id,primer_nombre',
            ]);
    }

    /**
     * @param  Builder<MovimientoStock>  $query
     * @param  array<string, mixed>  $filtros
     * @return Builder<MovimientoStock>
     */
    private function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        $fechaDesde = $this->fechaFiltro($filtros, 'fecha_desde');
        $fechaHasta = $this->fechaFiltro($filtros, 'fecha_hasta');

        return $query
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
            );
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function fechaFiltro(array $filtros, string $campo): ?string
    {
        $valor = $filtros[$campo] ?? null;

        return is_string($valor) ? $valor : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\BusinessLogic\Inventario\Data\Stock\StockMinimoData;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class ObtenerStockMinimo
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, StockMinimoData>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        $stockAlmacenes = $this->obtenerStockAlmacenes();
        $demandaHousekeeping = $this->obtenerDemandaHousekeeping();

        return $this->consultaProductos($filtros)
            ->get()
            ->map(fn ($row) => $this->mapearFila($row, $stockAlmacenes, $demandaHousekeeping))
            ->filter(fn ($item) => $item->estado !== 'Óptimo' || (isset($filtros['categoria_id']) && $filtros['categoria_id']))
            ->values();
    }

    /**
     * @return Collection<string, stdClass>
     */
    private function obtenerStockAlmacenes(): Collection
    {
        return DB::table('inv_stock')
            ->select([
                'producto_id',
                'producto_variante_id',
                DB::raw('SUM(cantidad) as total_almacen'),
            ])
            ->groupBy('producto_id', 'producto_variante_id')
            ->get()
            ->keyBy(fn ($item) => $item->producto_id.'-'.($item->producto_variante_id ?? '0'));
    }

    /**
     * @return Collection<string, stdClass>
     */
    private function obtenerDemandaHousekeeping(): Collection
    {
        return DB::table('stocks as s')
            ->join('producto_variantes as pv', 's.producto_variante_id', '=', 'pv.id')
            ->whereNull('s.deleted_at')
            ->select([
                'pv.producto_id',
                's.producto_variante_id',
                DB::raw('SUM(s.cantidad_ideal) as total_ideal'),
                DB::raw('SUM(s.cantidad_actual) as total_actual'),
            ])
            ->groupBy('pv.producto_id', 's.producto_variante_id')
            ->get()
            ->keyBy(fn ($item) => $item->producto_id.'-'.($item->producto_variante_id ?? '0'));
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function consultaProductos(array $filtros): Builder
    {
        return DB::table('producto_variantes as pv')
            ->join('productos as p', 'pv.producto_id', '=', 'p.id')
            ->leftJoin('catalogos as cat', 'p.categoria_id', '=', 'cat.id')
            ->whereNull('pv.deleted_at')
            ->whereNull('p.deleted_at')
            ->when(
                isset($filtros['categoria_id']) && $filtros['categoria_id'],
                fn ($q) => $q->where('p.categoria_id', $filtros['categoria_id'])
            )
            ->select([
                'p.id as producto_id',
                'p.nombre as producto',
                'pv.id as variante_id',
                'pv.nombre_variante as variante',
                'cat.nombre as categoria',
            ])
            ->orderBy('p.nombre');
    }

    /**
     * @param  Collection<string, stdClass>  $stockAlmacenes
     * @param  Collection<string, stdClass>  $demandaHousekeeping
     */
    private function mapearFila(stdClass $row, Collection $stockAlmacenes, Collection $demandaHousekeeping): StockMinimoData
    {
        $key = $row->producto_id.'-'.$row->variante_id;

        $stockActual = (float) ($stockAlmacenes->get($key)->total_almacen ?? 0.0);

        $ideal = (float) ($demandaHousekeeping->get($key)->total_ideal ?? 0.0);
        $actual = (float) ($demandaHousekeeping->get($key)->total_actual ?? 0.0);

        $pendiente = max(0.0, $ideal - $actual);
        $puntoPedido = $ideal > 0 ? $ideal : 10.0;

        $ratio = $puntoPedido > 0 ? ($stockActual / $puntoPedido) : 1.0;

        return new StockMinimoData(
            productoId: (int) $row->producto_id,
            producto: $row->producto,
            variante: $row->variante,
            categoria: $row->categoria,
            stockActual: $stockActual,
            puntoPedido: $puntoPedido,
            pendienteReplenish: $pendiente,
            estado: $this->resolverEstado($stockActual, $ratio),
        );
    }

    private function resolverEstado(float $stockActual, float $ratio): string
    {
        if ($stockActual <= 0) {
            return 'Crítico';
        }

        return $ratio <= 0.3 ? 'Reordenar' : 'Óptimo';
    }
}

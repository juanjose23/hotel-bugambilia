<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Stock;

use App\BusinessLogic\Inventario\Data\Stock\CostoVentasData;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class ObtenerAnalisisCostoVentas
{
    /**
     * @param  array<string, mixed>  $filtros
     * @return Collection<int, CostoVentasData>
     */
    public function ejecutar(array $filtros = []): Collection
    {
        [$fechaDesde, $fechaHasta] = $this->rangoFechas($filtros);

        $compras = $this->obtenerCompras($fechaDesde, $fechaHasta);
        $consumos = $this->obtenerConsumos($fechaDesde, $fechaHasta);

        return $this->consultaProductos()
            ->get()
            ->map(fn ($row) => $this->mapearFila($row, $compras, $consumos))
            ->filter(fn ($item) => $item->cantidadComprada > 0 || $item->cantidadConsumida > 0)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{Carbon, Carbon}
     */
    private function rangoFechas(array $filtros): array
    {
        $fechaDesdeStr = is_string($filtros['fecha_desde'] ?? null) ? $filtros['fecha_desde'] : null;
        $fechaHastaStr = is_string($filtros['fecha_hasta'] ?? null) ? $filtros['fecha_hasta'] : null;

        $fechaDesde = $fechaDesdeStr !== null
            ? Carbon::parse($fechaDesdeStr)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $fechaHasta = $fechaHastaStr !== null
            ? Carbon::parse($fechaHastaStr)->endOfDay()
            : Carbon::now()->endOfDay();

        return [$fechaDesde, $fechaHasta];
    }

    /**
     * @return Collection<string, stdClass>
     */
    private function obtenerCompras(Carbon $fechaDesde, Carbon $fechaHasta): Collection
    {
        return DB::table('recepcion_items as ri')
            ->join('recepciones_compra as rc', 'ri.recepcion_id', '=', 'rc.id')
            ->join('orden_compra_items as oci', 'ri.orden_item_id', '=', 'oci.id')
            ->leftJoin('ordenes_compra as oc', 'oci.orden_compra_id', '=', 'oc.id')
            ->whereNull('ri.deleted_at')
            ->whereNull('rc.deleted_at')
            ->whereBetween('rc.fecha_recepcion', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
            ->select([
                'ri.producto_id',
                'ri.producto_variante_id',
                DB::raw('SUM(ri.cantidad_recibida) as cant_comprada'),
                DB::raw('SUM(ri.cantidad_recibida * COALESCE(oci.precio_unitario * COALESCE(oc.tasa_cambio, 1.0), 0)) as costo_compra'),
            ])
            ->groupBy('ri.producto_id', 'ri.producto_variante_id')
            ->get()
            ->keyBy(fn ($item) => $item->producto_id.'-'.($item->producto_variante_id ?? '0'));
    }

    /**
     * @return Collection<string, stdClass>
     */
    private function obtenerConsumos(Carbon $fechaDesde, Carbon $fechaHasta): Collection
    {
        return DB::table('inv_movimientos as m')
            ->where('m.tipo', 'CONSUMO')
            ->whereBetween('m.created_at', [$fechaDesde, $fechaHasta])
            ->select([
                'm.producto_id',
                DB::raw('SUM(ABS(m.cantidad)) as cant_consumida'),
                DB::raw('SUM(COALESCE(m.costo_total, 0)) as costo_consumo'),
            ])
            ->groupBy('m.producto_id')
            ->get()
            ->keyBy(fn ($item) => (string) $item->producto_id);
    }

    private function consultaProductos(): Builder
    {
        return DB::table('producto_variantes as pv')
            ->join('productos as p', 'pv.producto_id', '=', 'p.id')
            ->leftJoin('catalogos as cat', 'p.categoria_id', '=', 'cat.id')
            ->whereNull('pv.deleted_at')
            ->whereNull('p.deleted_at')
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
     * @param  Collection<string, stdClass>  $compras
     * @param  Collection<string, stdClass>  $consumos
     */
    private function mapearFila(stdClass $row, Collection $compras, Collection $consumos): CostoVentasData
    {
        $key = $row->producto_id.'-'.$row->variante_id;

        $compra = $compras->get($key);
        $consumo = $consumos->get((string) $row->producto_id);

        $cantComprada = (float) ($compra->cant_comprada ?? 0.0);
        $costoCompra = (float) ($compra->costo_compra ?? 0.0);

        $cantConsumida = max(0.0, (float) ($consumo->cant_consumida ?? 0.0));
        $costoConsumo = max(0.0, (float) ($consumo->costo_consumo ?? 0.0));

        $desviacion = 0.0;
        if ($costoCompra > 0) {
            $desviacion = (($costoConsumo - $costoCompra) / $costoCompra) * 100;
        }

        return new CostoVentasData(
            productoId: (int) $row->producto_id,
            producto: $row->producto,
            variante: $row->variante,
            categoria: $row->categoria,
            cantidadComprada: $cantComprada,
            costoCompras: $costoCompra,
            cantidadConsumida: $cantConsumida,
            costoConsumo: $costoConsumo,
            desviacionPorcentaje: $desviacion,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Reportes;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ObtenerReportesRestauranteQuery
{
    /**
     * @return Collection<int, stdClass>
     */
    public function obtenerPedidosPorRango(string $fechaInicio, string $fechaFin): Collection
    {
        $inicio = $fechaInicio.' 00:00:00';
        $fin = $fechaFin.' 23:59:59';

        return DB::table('pedidos')
            ->select('id', 'codigo', 'estado', 'subtotal', 'total', 'created_at')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$inicio, $fin])
            ->get();
    }

    /**
     * @return Collection<int, stdClass>
     */
    public function obtenerItemsPorRango(string $fechaInicio, string $fechaFin): Collection
    {
        $inicio = $fechaInicio.' 00:00:00';
        $fin = $fechaFin.' 23:59:59';

        return DB::table('pedido_items as pi')
            ->join('pedidos as p', 'pi.pedido_id', '=', 'p.id')
            ->leftJoin('platos as pl', 'pi.plato_id', '=', 'pl.id')
            ->leftJoin('productos as pr', 'pi.producto_id', '=', 'pr.id')
            ->leftJoin('producto_variantes as pv', 'pi.producto_variante_id', '=', 'pv.id')
            ->leftJoin('catalogos as c', fn ($join) => $join->on('c.id', '=', DB::raw('COALESCE(pl.categoria_id, pr.categoria_id)')))
            ->whereNull('p.deleted_at')
            ->where('p.estado', '!=', EstadoPedido::CANCELADO->value)
            ->where('pi.estado', '!=', EstadoItemPedido::ANULADO->value)
            ->whereBetween('p.created_at', [$inicio, $fin])
            ->select('pi.plato_id', 'pi.producto_id', 'pi.producto_variante_id', 'pl.nombre as plato_nombre', 'pr.nombre as producto_nombre', 'pv.nombre_variante as variante_nombre', 'c.nombre as categoria_nombre')
            ->selectRaw('SUM(pi.cantidad) as cantidad, SUM(pi.subtotal) as subtotal')
            ->groupBy('pi.plato_id', 'pi.producto_id', 'pi.producto_variante_id', 'pl.nombre', 'pr.nombre', 'pv.nombre_variante', 'c.nombre')
            ->get();
    }

    /**
     * @return Builder<Pedido>
     */
    public function pedidosParaTabla(string $fechaInicio, string $fechaFin): Builder
    {
        return Pedido::query()
            ->with(['mesa', 'mesero.persona'])
            ->whereBetween('created_at', [$fechaInicio.' 00:00:00', $fechaFin.' 23:59:59'])
            ->latest();
    }
}

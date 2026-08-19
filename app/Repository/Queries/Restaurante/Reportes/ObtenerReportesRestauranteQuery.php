<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Reportes;

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
            ->leftJoin('catalogos as c', 'pl.categoria_id', '=', 'c.id')
            ->whereNull('p.deleted_at')
            ->whereNull('pl.deleted_at')
            ->whereBetween('p.created_at', [$inicio, $fin])
            ->select(
                'pi.id',
                'pi.pedido_id',
                'pi.plato_id',
                'pl.nombre as plato_nombre',
                'c.nombre as categoria_nombre',
                'pi.cantidad',
                'pi.precio_unitario',
                'pi.subtotal',
            )
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

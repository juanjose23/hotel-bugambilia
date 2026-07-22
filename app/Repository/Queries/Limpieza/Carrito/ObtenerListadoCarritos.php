<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Illuminate\Database\Eloquent\Builder;

class ObtenerListadoCarritos
{
    /**
     * Configura el query builder con las subconsultas necesarias para el listado.
     *
     * @param  Builder<Ubicacion>  $query
     * @return Builder<Ubicacion>
     */
    public function execute(Builder $query): Builder
    {
        $insumosSub = Stock::selectRaw('COALESCE(COUNT(*), 0)')
            ->whereColumn('ubicacion_id', 'ubicaciones.id')
            ->where('cantidad', '>', 0);

        $bloqueadoSub = LimpiezaEjecucion::selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
            ->whereColumn('carrito_id', 'ubicaciones.id')
            ->where('estado', EstadoLimpieza::EnProgreso);

        return $query
            ->addSelect([
                'insumos_count' => $insumosSub,
                'bloqueado' => $bloqueadoSub,
            ]);
    }
}

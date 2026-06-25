<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesCuarentena;
use Illuminate\Contracts\View\View;

class LotesCuarentenaExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawProductoId = $this->filtros['producto_id'] ?? null;
        $rawFechaDesde = $this->filtros['fecha_desde'] ?? null;

        $filtros = [
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
            'fecha_desde' => is_string($rawFechaDesde) ? $rawFechaDesde : null,
        ];
        $lotes = app(ObtenerLotesCuarentena::class)->ejecutar($filtros);

        return view('exports.inventario.cuarentena', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

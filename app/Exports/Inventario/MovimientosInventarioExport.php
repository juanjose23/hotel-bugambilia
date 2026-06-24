<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerMovimientosInventario;
use Illuminate\Contracts\View\View;

class MovimientosInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawTipo = $this->filtros['tipo'] ?? null;
        $rawProductoId = $this->filtros['producto_id'] ?? null;
        $rawLoteId = $this->filtros['lote_id'] ?? null;
        $rawFechaDesde = $this->filtros['fecha_desde'] ?? null;
        $rawFechaHasta = $this->filtros['fecha_hasta'] ?? null;

        $filtros = [
            'tipo' => is_string($rawTipo) ? $rawTipo : null,
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
            'lote_id' => is_numeric($rawLoteId) ? (int) $rawLoteId : null,
            'fecha_desde' => is_string($rawFechaDesde) ? $rawFechaDesde : null,
            'fecha_hasta' => is_string($rawFechaHasta) ? $rawFechaHasta : null,
        ];
        $data = app(ObtenerMovimientosInventario::class)->ejecutar($filtros, 5000)->items();

        return view('exports.inventario.movimientos', ['movimientos' => $data, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use Illuminate\Contracts\View\View;

class StockPorProductoExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawProductoId = $this->filtros['producto_id'] ?? null;
        $rawUbicacionId = $this->filtros['ubicacion_id'] ?? null;

        $filtros = [
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
            'ubicacion_id' => is_numeric($rawUbicacionId) ? (int) $rawUbicacionId : null,
        ];

        return view('exports.inventario.stock-por-producto', [
            'filas' => app(ObtenerStockPorProducto::class)->ejecutar($filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

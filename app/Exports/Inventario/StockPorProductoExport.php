<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerStockPorProducto;
use Illuminate\Contracts\View\View;

class StockPorProductoExport extends BaseInventarioExport
{
    public function view(): View
    {
        return view('exports.inventario.stock-por-producto', [
            'filas' => app(ObtenerStockPorProducto::class)->ejecutar($this->filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerMovimientosInventario;
use Illuminate\Contracts\View\View;

class MovimientosInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $data = app(ObtenerMovimientosInventario::class)->ejecutar($this->filtros, 5000)->items();

        return view('exports.inventario.movimientos', ['movimientos' => $data, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Mermas\ObtenerMermasTotales;
use Illuminate\Contracts\View\View;

class MermasTotalesExport extends BaseInventarioExport
{
    public function view(): View
    {
        $uc = app(ObtenerMermasTotales::class);

        return view('exports.inventario.mermas-totales', [
            'filas' => $uc->ejecutar($this->filtros),
            'totalPerdidas' => $uc->totalPerdidas($this->filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

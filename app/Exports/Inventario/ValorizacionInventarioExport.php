<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use Illuminate\Contracts\View\View;

class ValorizacionInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $uc = app(ObtenerValorizacionInventario::class);

        return view('exports.inventario.valorizacion', [
            'filas' => $uc->ejecutar($this->filtros),
            'totalGeneral' => $uc->totalGeneral($this->filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

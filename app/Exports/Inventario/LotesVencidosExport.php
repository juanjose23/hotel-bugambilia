<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesVencidos;
use Illuminate\Contracts\View\View;

class LotesVencidosExport extends BaseInventarioExport
{
    public function view(): View
    {
        $lotes = app(ObtenerLotesVencidos::class)->ejecutar($this->filtros);

        return view('exports.inventario.vencidos', [
            'lotes' => $lotes,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

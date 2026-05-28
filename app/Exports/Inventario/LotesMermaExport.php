<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Mermas\ObtenerLotesMerma;
use Illuminate\Contracts\View\View;

class LotesMermaExport extends BaseInventarioExport
{
    public function view(): View
    {
        $lotes = app(ObtenerLotesMerma::class)->ejecutar($this->filtros);

        return view('exports.inventario.mermas', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

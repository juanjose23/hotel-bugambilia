<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesProximosVencer;
use Illuminate\Contracts\View\View;

class LotesProximosVencerExport extends BaseInventarioExport
{
    public function view(): View
    {
        $lotes = app(ObtenerLotesProximosVencer::class)->ejecutar($this->filtros);

        return view('exports.inventario.proximos-vencer', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i'), 'dias' => $this->filtros['dias'] ?? 30]);
    }
}

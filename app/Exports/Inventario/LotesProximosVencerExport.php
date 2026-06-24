<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesProximosVencer;
use Illuminate\Contracts\View\View;

class LotesProximosVencerExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawDias = $this->filtros['dias'] ?? null;
        $rawProductoId = $this->filtros['producto_id'] ?? null;

        $filtros = [
            'dias' => is_numeric($rawDias) ? (int) $rawDias : 30,
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
        ];
        $lotes = app(ObtenerLotesProximosVencer::class)->ejecutar($filtros);

        return view('exports.inventario.proximos-vencer', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i'), 'dias' => $filtros['dias']]);
    }
}

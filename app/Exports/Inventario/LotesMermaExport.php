<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Mermas\ObtenerLotesMerma;
use Illuminate\Contracts\View\View;

class LotesMermaExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawPeriodoDesde = $this->filtros['periodo_desde'] ?? null;
        $rawPeriodoHasta = $this->filtros['periodo_hasta'] ?? null;
        $rawMotivo = $this->filtros['motivo'] ?? null;

        $filtros = [
            'periodo_desde' => is_string($rawPeriodoDesde) ? $rawPeriodoDesde : null,
            'periodo_hasta' => is_string($rawPeriodoHasta) ? $rawPeriodoHasta : null,
            'motivo' => is_string($rawMotivo) ? $rawMotivo : null,
        ];
        $lotes = app(ObtenerLotesMerma::class)->ejecutar($filtros);

        return view('exports.inventario.mermas', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Mermas\ObtenerMermasTotales;
use Illuminate\Contracts\View\View;

class MermasTotalesExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawPeriodoDesde = $this->filtros['periodo_desde'] ?? null;
        $rawPeriodoHasta = $this->filtros['periodo_hasta'] ?? null;

        $filtros = [
            'periodo_desde' => is_string($rawPeriodoDesde) ? $rawPeriodoDesde : null,
            'periodo_hasta' => is_string($rawPeriodoHasta) ? $rawPeriodoHasta : null,
        ];
        $uc = app(ObtenerMermasTotales::class);

        return view('exports.inventario.mermas-totales', [
            'filas' => $uc->ejecutar($filtros),
            'totalPerdidas' => $uc->totalPerdidas($filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

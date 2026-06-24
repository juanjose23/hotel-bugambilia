<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesVencidos;
use Illuminate\Contracts\View\View;

class LotesVencidosExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawProductoId = $this->filtros['producto_id'] ?? null;

        $filtros = [
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
        ];
        $lotes = app(ObtenerLotesVencidos::class)->ejecutar($filtros);

        return view('exports.inventario.vencidos', [
            'lotes' => $lotes,
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

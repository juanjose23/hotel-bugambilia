<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use Illuminate\Contracts\View\View;

class TrazabilidadLoteExport extends BaseInventarioExport
{
    public function view(): View
    {
        $loteId = (int) ($this->filtros['lote_id'] ?? 0);
        $data = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar($loteId);

        return view('exports.inventario.trazabilidad', [
            'lote' => $data['lote'],
            'movimientos' => $data['movimientos'],
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

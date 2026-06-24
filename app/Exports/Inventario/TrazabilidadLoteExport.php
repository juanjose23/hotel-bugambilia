<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\UseCases\Inventario\Queries\Trazabilidad\TrazabilidadLoteHaciaAdelante;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;

class TrazabilidadLoteExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawLoteId = $this->filtros['lote_id'] ?? null;
        $loteId = is_numeric($rawLoteId) ? (int) $rawLoteId : 0;

        /** @var array{lote: Lote, movimientos: Collection<int, MovimientoStock>} $data */
        $data = app(TrazabilidadLoteHaciaAdelante::class)->ejecutar($loteId);

        return view('exports.inventario.trazabilidad', [
            'lote' => $data['lote'],
            'movimientos' => $data['movimientos'],
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

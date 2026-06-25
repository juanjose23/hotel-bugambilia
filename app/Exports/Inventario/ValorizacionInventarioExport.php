<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Stock\ObtenerValorizacionInventario;
use Illuminate\Contracts\View\View;

class ValorizacionInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawUbicacionId = $this->filtros['ubicacion_id'] ?? null;
        $rawProductoId = $this->filtros['producto_id'] ?? null;

        $filtros = [
            'ubicacion_id' => is_numeric($rawUbicacionId) ? (int) $rawUbicacionId : null,
            'producto_id' => is_numeric($rawProductoId) ? (int) $rawProductoId : null,
        ];
        $uc = app(ObtenerValorizacionInventario::class);

        return view('exports.inventario.valorizacion', [
            'filas' => $uc->ejecutar($filtros),
            'totalGeneral' => $uc->totalGeneral($filtros),
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }
}

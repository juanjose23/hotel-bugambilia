<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Gestion\ObtenerRotacionInventario;
use Illuminate\Contracts\View\View;

class RotacionInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $rawMeses = $this->filtros['meses'] ?? null;

        $filtros = [
            'meses' => is_numeric($rawMeses) ? (int) $rawMeses : 3,
        ];
        $filas = app(ObtenerRotacionInventario::class)->ejecutar($filtros);

        return view('exports.inventario.rotacion', ['filas' => $filas, 'fecha' => now()->format('d/m/Y H:i'), 'meses' => $filtros['meses']]);
    }
}

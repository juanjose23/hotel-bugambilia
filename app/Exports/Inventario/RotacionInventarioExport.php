<?php

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Gestion\ObtenerRotacionInventario;
use Illuminate\Contracts\View\View;

class RotacionInventarioExport extends BaseInventarioExport
{
    public function view(): View
    {
        $filas = app(ObtenerRotacionInventario::class)->ejecutar($this->filtros);

        return view('exports.inventario.rotacion', ['filas' => $filas, 'fecha' => now()->format('d/m/Y H:i'), 'meses' => $this->filtros['meses'] ?? 3]);
    }
}

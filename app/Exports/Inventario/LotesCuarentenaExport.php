<?php

declare(strict_types=1);

namespace App\Exports\Inventario;

use App\UseCases\Inventario\Queries\Alertas\ObtenerLotesCuarentena;
use Illuminate\Contracts\View\View;

class LotesCuarentenaExport extends BaseInventarioExport
{
    public function view(): View
    {
        $lotes = app(ObtenerLotesCuarentena::class)->ejecutar($this->filtros);

        return view('exports.inventario.cuarentena', ['lotes' => $lotes, 'fecha' => now()->format('d/m/Y H:i')]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Lotes;

use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\Inventario\Lote;

class ContarLotesCuarentenaQuery
{
    public function ejecutar(): int
    {
        return Lote::query()
            ->where('estado', EstadoLote::Cuarentena)
            ->count();
    }
}

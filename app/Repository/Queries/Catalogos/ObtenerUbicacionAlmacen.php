<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;

class ObtenerUbicacionAlmacen
{
    public function ejecutar(): ?Ubicacion
    {
        return Ubicacion::where('tipo', 'almacen')
            ->where('estado', 1)
            ->first()
            ?? Ubicacion::where('estado', 1)->first();
    }
}

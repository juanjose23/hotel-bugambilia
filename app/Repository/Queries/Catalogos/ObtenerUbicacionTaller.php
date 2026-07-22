<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Support\Facades\DB;

class ObtenerUbicacionTaller
{
    public function ejecutar(): ?Ubicacion
    {
        $driver = DB::connection()->getDriverName();
        $likeOperator = $driver === 'sqlite' ? 'like' : 'ilike';

        $taller = Ubicacion::where(function ($query) use ($likeOperator) {
            $query->where('nombre', $likeOperator, '%mantenimiento%')
                ->orWhere('nombre', $likeOperator, '%taller%');
        })
            ->where('estado', 1)
            ->first();

        if ($taller) {
            return $taller;
        }

        return Ubicacion::where('tipo', 'almacen')
            ->where('estado', 1)
            ->first()
            ?? Ubicacion::where('estado', 1)->first();
    }
}

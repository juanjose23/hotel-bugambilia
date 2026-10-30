<?php

declare(strict_types=1);

namespace App\Repository\Queries\Habitaciones;

use App\Repository\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\Builder;

final readonly class ObtenerVistasHabitacionQuery
{
    /** @return array<int, string> */
    public function opciones(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Catalogo::query()->whereHas('catalogoTipo', fn (Builder $q) => $q->whereRaw('upper(codigo) = ?', ['TIPO_VISTA']))
            ->orderBy('nombre')->pluck('nombre', 'id')->all();

        return $opciones;
    }
}

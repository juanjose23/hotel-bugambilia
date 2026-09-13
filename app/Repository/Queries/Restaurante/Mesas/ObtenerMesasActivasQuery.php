<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Support\Collection;

final class ObtenerMesasActivasQuery
{
    /**
     * @return Collection<int, Espacio>
     */
    public function ejecutar(): Collection
    {
        return Espacio::query()
            ->with(['ubicacion'])
            ->where('tipo', TipoEspacio::MESA)
            ->where('estado', EstadoEspacio::Disponible)
            ->get();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Turno;

use App\Repository\Models\Limpieza\Turno;

final class ObtenerTurnoPorId
{
    public function execute(int $turnoId, bool $conEquipo = false): ?Turno
    {
        $query = Turno::query();

        if ($conEquipo) {
            $query->with([
                'lider.persona.personaNatural',
                'apoyo.persona.personaNatural',
            ]);
        }

        return $query->find($turnoId);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class ObtenerColaboradorAsignadoParaOpciones
{
    public function execute(int $ejecucionId): ?Colaborador
    {
        $ejecucion = LimpiezaEjecucion::query()
            ->with(['colaborador.persona.personaNatural', 'solicitud.personal.persona.colaborador.persona.personaNatural'])
            ->find($ejecucionId);

        return $ejecucion?->colaborador
            ?: $ejecucion?->solicitud?->personal?->persona?->colaborador;
    }
}

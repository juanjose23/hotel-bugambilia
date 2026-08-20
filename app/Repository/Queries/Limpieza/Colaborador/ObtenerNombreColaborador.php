<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Colaborador;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

final class ObtenerNombreColaborador
{
    public function execute(mixed $colaboradorId): string
    {
        if (! is_numeric($colaboradorId)) {
            return is_scalar($colaboradorId) || $colaboradorId === null ? (string) $colaboradorId : '';
        }

        $colaborador = Colaborador::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->find((int) $colaboradorId);

        if (! $colaborador) {
            return "Colaborador #{$colaboradorId}";
        }

        return $colaborador->persona
            ? ObtenerNombrePersona::desde($colaborador->persona)
            : "Colaborador #{$colaborador->id}";
    }
}

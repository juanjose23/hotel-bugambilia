<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Shared;

use App\Repository\Models\Colaboradores\Colaborador;

final class ObtenerColaboradorDeSesion
{
    public function ejecutar(bool $lanzarSiNoExiste = true): ?Colaborador
    {
        $personaId = auth()->user()?->persona_id;

        if ($personaId === null) {
            return $this->manejarResultado(null, $lanzarSiNoExiste);
        }

        $colaborador = Colaborador::where('persona_id', $personaId)->first();

        return $this->manejarResultado($colaborador, $lanzarSiNoExiste);
    }

    private function manejarResultado(?Colaborador $colaborador, bool $lanzar): ?Colaborador
    {
        if ($colaborador === null && $lanzar) {
            throw new \RuntimeException('El usuario actual no tiene un colaborador asignado.');
        }

        return $colaborador;
    }
}

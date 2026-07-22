<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

use App\Repository\Models\Colaboradores\Colaborador;

class ObtenerOpcionesColaborador
{
    /** @return array<int|string, string> */
    public function ejecutar(): array
    {
        $colaboradores = Colaborador::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->get();

        $opciones = [];
        foreach ($colaboradores as $c) {
            $name = $c->persona
                ? ObtenerNombrePersona::desde($c->persona)
                : "Colaborador #{$c->id}";

            $opciones[$c->id] = $name;
        }

        return $opciones;
    }
}

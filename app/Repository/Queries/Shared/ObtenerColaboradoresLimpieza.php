<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

use App\Repository\Models\Colaboradores\Colaborador;

final class ObtenerColaboradoresLimpieza
{
    /**
     * @return array<int, string>
     */
    public static function opciones(): array
    {
        $query = Colaborador::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->orderBy('id');

        /** @var array<int, string> $options */
        $options = $query
            ->get()
            ->mapWithKeys(function (Colaborador $c) {
                $name = $c->persona
                    ? ObtenerNombrePersona::desde($c->persona)
                    : "Colaborador #{$c->id}";

                return [$c->id => $name];
            })
            ->toArray();

        return $options;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Shared;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\User;

final class ObtenerColaboradoresLimpieza
{
    /**
     * @return array<int, string>
     */
    public static function opciones(): array
    {
        $personasIds = User::permission('Update:LimpiezaEjecucion')
            ->whereNotNull('persona_id')
            ->distinct()
            ->pluck('persona_id');

        /** @var array<int, string> $options */
        $options = Colaborador::query()
            ->whereIn('persona_id', $personasIds)
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
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

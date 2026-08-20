<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Turno;

use App\Repository\Models\Limpieza\Turno;

final class ObtenerOpcionesTurnosActivos
{
    /**
     * @return array<int, string>
     */
    public function execute(bool $incluirHorario = false): array
    {
        $opciones = Turno::query()
            ->where('estado', true)
            ->orderBy('hora_inicio')
            ->get()
            ->mapWithKeys(fn (Turno $turno): array => [
                (int) $turno->id => $incluirHorario
                    ? "{$turno->nombre} ({$turno->hora_inicio} - {$turno->hora_fin})"
                    : (string) $turno->nombre,
            ])
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}

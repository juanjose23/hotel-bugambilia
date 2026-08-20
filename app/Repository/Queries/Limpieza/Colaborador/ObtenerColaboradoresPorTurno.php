<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Colaborador;

use App\Repository\Models\Limpieza\Turno;
use App\Repository\Queries\Limpieza\Turno\ObtenerTurnoPorId;
use App\Repository\Queries\Shared\ObtenerColaboradoresLimpieza;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

final class ObtenerColaboradoresPorTurno
{
    public function __construct(
        private readonly ObtenerTurnoPorId $obtenerTurno,
    ) {}

    /**
     * @return array<int, string>
     */
    public function execute(mixed $turnoId): array
    {
        if (! is_numeric($turnoId)) {
            return ObtenerColaboradoresLimpieza::opciones();
        }

        $turno = $this->obtenerTurno->execute((int) $turnoId, conEquipo: true);

        if (! $turno instanceof Turno) {
            return ObtenerColaboradoresLimpieza::opciones();
        }

        $colaboradores = collect();

        if ($turno->lider) {
            $colaboradores->push($turno->lider);
        }

        if ($turno->apoyo) {
            $colaboradores->push($turno->apoyo);
        }

        if ($colaboradores->isEmpty()) {
            return ObtenerColaboradoresLimpieza::opciones();
        }

        $opciones = $colaboradores
            ->mapWithKeys(function ($colaborador): array {
                $nombre = $colaborador->persona
                    ? ObtenerNombrePersona::desde($colaborador->persona)
                    : "Colaborador #{$colaborador->id}";

                return [(int) $colaborador->id => $nombre];
            })
            ->toArray();

        /** @var array<int, string> $opciones */
        return $opciones;
    }
}

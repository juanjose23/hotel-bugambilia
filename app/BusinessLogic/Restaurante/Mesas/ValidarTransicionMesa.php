<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use DomainException;

final class ValidarTransicionMesa
{
    /** @return EstadoEspacio[] */
    private function permitidos(EstadoEspacio $actual): array
    {
        return match ($actual) {
            EstadoEspacio::Disponible => [EstadoEspacio::Ocupado, EstadoEspacio::Mantenimiento, EstadoEspacio::Reservado, EstadoEspacio::Inactivo],
            EstadoEspacio::Ocupado => [EstadoEspacio::Sucio, EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Sucio => [EstadoEspacio::Ocupado, EstadoEspacio::Limpieza, EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Limpieza => [EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Mantenimiento => [EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Reservado => [EstadoEspacio::Ocupado, EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Inactivo => [EstadoEspacio::Disponible],
        };
    }

    public function validar(EstadoEspacio $actual, EstadoEspacio $nuevo): void
    {
        $permitidos = $this->permitidos($actual);

        if (! in_array($nuevo, $permitidos, true)) {
            throw new DomainException(
                "Transición no permitida: de '{$actual->getLabel()}' a '{$nuevo->getLabel()}'."
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\BusinessLogic\Espacios;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use DomainException;

final class ValidarEspacioOperativoConActivo
{
    /**
     * Valida que un espacio que requiere equipamiento físico obligatorio
     * (por ejemplo, Mesas) cuente con al menos un Activo Fijo asignado y vigente.
     *
     * @throws DomainException Si el espacio no cumple con el equipamiento obligatorio.
     */
    public function validar(Espacio $espacio): void
    {
        if ($espacio->tipo === TipoEspacio::MESA) {
            $espacio->loadMissing('inventarioFijo');

            if ($espacio->inventarioFijo->isEmpty()) {
                throw new DomainException(
                    "El espacio '{$espacio->nombre}' (Tipo: {$espacio->tipo->getLabel()}) no puede ser utilizado porque no tiene un activo físico (Mobiliario) asignado."
                );
            }
        }
    }

    /**
     * Comprueba si el espacio cuenta con los activos físicos necesarios para operar.
     */
    public function esOperable(Espacio $espacio): bool
    {
        if ($espacio->tipo === TipoEspacio::MESA) {
            $espacio->loadMissing('inventarioFijo');

            return $espacio->inventarioFijo->isNotEmpty();
        }

        return true;
    }
}

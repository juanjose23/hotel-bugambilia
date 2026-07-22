<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Enums\Compras\EstadoRecepcion;

final class ValidarTransicionRecepcion
{
    public function esPermitida(EstadoRecepcion $estadoActual, EstadoRecepcion $nuevoEstado): bool
    {
        if ($estadoActual === EstadoRecepcion::Pendiente) {
            return $nuevoEstado !== EstadoRecepcion::Pendiente;
        }

        return false;
    }
}

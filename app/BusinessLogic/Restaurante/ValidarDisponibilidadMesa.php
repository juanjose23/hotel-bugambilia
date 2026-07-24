<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use DomainException;

final class ValidarDisponibilidadMesa
{
    public function validar(Espacio $mesa): void
    {
        if ($mesa->estado !== EstadoEspacio::Disponible) {
            throw new DomainException("La mesa '{$mesa->nombre}' no está disponible para abrir una nueva comanda (Estado actual: {$mesa->estado->getLabel()}).");
        }
    }
}

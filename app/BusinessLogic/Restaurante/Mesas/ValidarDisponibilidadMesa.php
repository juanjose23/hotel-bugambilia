<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\BusinessLogic\Espacios\ValidarEspacioOperativoConActivo;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use DomainException;

final class ValidarDisponibilidadMesa
{
    public function __construct(
        private readonly ValidarEspacioOperativoConActivo $validarOperativoConActivo,
    ) {}

    public function validar(Espacio $mesa): void
    {
        if ($mesa->estado !== EstadoEspacio::Disponible) {
            throw new DomainException("La mesa '{$mesa->nombre}' no está disponible para abrir una nueva comanda (Estado actual: {$mesa->estado->getLabel()}).");
        }

        $this->validarOperativoConActivo->validar($mesa);
    }
}

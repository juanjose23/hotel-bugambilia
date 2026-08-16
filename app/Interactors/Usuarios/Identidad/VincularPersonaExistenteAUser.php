<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;

final readonly class VincularPersonaExistenteAUser
{
    public function __construct(
        private VincularPersonaAUser $vincularPersona,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(Persona $persona, array $datos): User
    {
        return $this->vincularPersona->ejecutar($persona, $datos);
    }
}

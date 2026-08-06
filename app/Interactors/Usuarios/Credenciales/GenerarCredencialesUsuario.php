<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Credenciales;

use App\BusinessLogic\Usuarios\GeneradorCredenciales;
use App\Repository\Models\Personas\Persona;

class GenerarCredencialesUsuario
{
    public function __construct(
        private readonly GeneradorCredenciales $generador,
    ) {}

    /** @return array{name: string, email: string} */
    public function execute(Persona $persona): array
    {
        return $this->generador->generar($persona);
    }
}

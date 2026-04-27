<?php

namespace App\UseCases\Personas;

use App\Models\Persona;

class CrearPersona
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Persona
    {
        return Persona::create($data);
    }
}
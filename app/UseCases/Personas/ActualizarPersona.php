<?php

namespace App\UseCases\Personas;

use App\Models\Persona;

class ActualizarPersona
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(Persona $model, array $data): Persona
    {
        $model->update($data);
        return $model;
    }
}
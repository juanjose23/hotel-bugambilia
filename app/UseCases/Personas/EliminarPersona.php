<?php

namespace App\UseCases;

use App\Models\Persona;

class EliminarPersona
{
    public function execute(Persona $model): bool
    {
        return $model->delete();
    }
}
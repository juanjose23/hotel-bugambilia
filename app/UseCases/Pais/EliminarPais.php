<?php

namespace App\UseCases\Pais;

use App\Models\Pais;

class EliminarPais
{
    public function execute(Pais $model): bool
    {
        return $model->delete();
    }
}

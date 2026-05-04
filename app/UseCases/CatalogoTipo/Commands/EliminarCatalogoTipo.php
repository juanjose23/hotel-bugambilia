<?php

namespace App\UseCases\CatalogoTipo\Commands;

use App\Models\Catalogos\CatalogoTipo;

class EliminarCatalogoTipo
{

    public function execute(CatalogoTipo $model): bool
    {
        return $model->delete();
    }
}

<?php

namespace App\UseCases\Catalogo\Commands ;

use App\Models\Catalogos\Catalogo;

class EliminarCatalogo
{
    public function execute(Catalogo $model): bool
    {
        return $model->delete();
    }
}

<?php

namespace App\UseCases\Catalogo\Commands;

use App\Models\Catalogos\Catalogo;

class ActualizarCatalogo
{
    public function execute(Catalogo $model, array $data): Catalogo
    {
        $model->update($data);
        return $model;
    }
}
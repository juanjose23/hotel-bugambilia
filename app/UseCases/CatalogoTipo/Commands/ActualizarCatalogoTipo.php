<?php

namespace App\UseCases\CatalogoTipo\Commands;

use App\Models\Catalogos\CatalogoTipo;

class ActualizarCatalogoTipo
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(CatalogoTipo $model, array $data): CatalogoTipo
    {
        $model->update($data);
        return $model;
    }
}

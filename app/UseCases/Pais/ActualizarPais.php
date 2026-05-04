<?php

namespace App\UseCases\Pais;

use App\Models\Catalogos\Pais;

class ActualizarPais
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(Pais $model, array $data): Pais
    {
        $model->update($data);
        return $model;
    }
}

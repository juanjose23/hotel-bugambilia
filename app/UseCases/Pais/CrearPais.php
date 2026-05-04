<?php

namespace App\UseCases\Pais;

use App\Models\Catalogos\Pais;

class CrearPais
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): Pais
    {
        return Pais::create($data);
    }
}

<?php

namespace App\UseCases\Catalogo\Commands;

use App\Models\Catalogos\Catalogo;

class CrearCatalogo
{
    public function execute(array $data): Catalogo
    {
        return Catalogo::create($data);
    }
}
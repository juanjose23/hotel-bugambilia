<?php

namespace App\UseCases\CatalogoTipo\Commands;

use App\Models\Catalogos\CatalogoTipo;

class CrearCatalogoTipo
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): CatalogoTipo
    {
        return CatalogoTipo::create($data);
    }
}

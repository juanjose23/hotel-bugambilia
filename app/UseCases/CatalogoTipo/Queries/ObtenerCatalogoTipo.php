<?php

namespace App\UseCases\CatalogoTipo\Queries;

use App\Models\Catalogos\CatalogoTipo;
use InvalidArgumentException;

class ObtenerCatalogoTipo
{
    public function execute(array $data): CatalogoTipo
    {
        $id = ($data['id'] ?? null);

        if (is_null($id)) {
            throw new InvalidArgumentException('Se requiere el identificador del tipo de catálogo');
        }

        return CatalogoTipo::with(['catalogos'])->findOrFail($id);
    }
}
<?php

namespace App\UseCases\CatalogoTipo\Queries;

use App\Models\Catalogos\CatalogoTipo;

class ListarCatalogoTipoOptions
{
    /**
     * Devuelve un array id => nombre para usar en selects.
     *
     * @return array<int,string>
     */
    public function execute(): array
    {
        return CatalogoTipo::query()
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();
    }
}

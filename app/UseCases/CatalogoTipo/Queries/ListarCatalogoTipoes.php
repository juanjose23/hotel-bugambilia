<?php

namespace App\UseCases\CatalogoTipo\Queries;

use App\Models\Catalogos\CatalogoTipo;
use Illuminate\Database\Eloquent\Builder;

class ListarCatalogoTipoes
{

    /**
     * @return Builder
     */
    public function execute(array $data): Builder
    {
        return CatalogoTipo::query();
    }
}

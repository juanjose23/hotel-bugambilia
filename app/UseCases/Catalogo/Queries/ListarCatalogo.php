<?php

namespace App\UseCases\Catalogo\Queries;

use App\Models\Catalogos\Catalogo;

use Illuminate\Database\Eloquent\Builder;

class ListarCatalogo
{
    /**
     * @return Builder
     */
    public function execute(array $data): Builder
    {
        return Catalogo::query();
    }
}
<?php

namespace App\UseCases\Catalogo\Queries;

use App\Models\Catalogos\Catalogo;

class ListarCatalogoOptions
{
    /**
     * Devuelve un array id => nombre para usar en selects.
     * Si se proporciona catalogo_tipo_id, filtra por ese tipo.
     *
     * @param array $data ['catalogo_tipo_id' => int|null]
     * @return array<int,string>
     */
    public function execute(array $data = []): array
    {
        $query = Catalogo::query();

        if (!empty($data['catalogo_tipo_id'])) {
            $query->where('catalogo_tipo_id', $data['catalogo_tipo_id']);
        }

        return $query->orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }
}

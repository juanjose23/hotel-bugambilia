<?php

namespace App\UseCases\Catalogo\Queries;

use App\Exceptions\RecursoNoEncontradoException;
use App\Models\Catalogos\Catalogo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ObtenerCatalogo
{
    public function execute(array $data): Catalogo
    {
        // Aceptamos tanto ['id'=>1] como solo el id en el valor principal.
        $id = is_array($data) ? ($data['id'] ?? null) : $data;

        if (is_null($id) || !is_numeric($id)) {
            throw new RecursoNoEncontradoException("El identificador del catálogo proporcionado no es válido.");
        }

        try {
            return Catalogo::with(['catalogoTipo', 'padre', 'children'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new RecursoNoEncontradoException("No se pudo localizar el catálogo solicitado.");
        }
    }
}
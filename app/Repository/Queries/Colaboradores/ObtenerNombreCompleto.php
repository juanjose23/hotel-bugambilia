<?php

declare(strict_types=1);

namespace App\Repository\Queries\Colaboradores;

use App\Repository\Models\Colaboradores\Colaborador;

class ObtenerNombreCompleto
{
    public function obtenerNombreCompleto(Colaborador $colaborador): string
    {
        return trim(
            ($colaborador->persona->primer_nombre ?? '').' '.
            ($colaborador->persona->segundo_nombre ?? '').' '.
            ($colaborador->persona->personaNatural->primer_apellido ?? '').' '.
            ($colaborador->persona->personaNatural->segundo_apellido ?? '')
        );
    }

    public function nombreCompletoConCodigo(Colaborador $colaborador): string
    {
        $nombre = $this->obtenerNombreCompleto($colaborador);

        return trim(($colaborador->codigo ?? '').' - '.$nombre);
    }
}

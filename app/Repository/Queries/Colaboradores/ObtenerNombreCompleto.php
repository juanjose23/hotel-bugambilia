<?php

declare(strict_types=1);

namespace App\Repository\Queries\Colaboradores;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Queries\Shared\ObtenerNombrePersona;

class ObtenerNombreCompleto
{
    public function obtenerNombreCompleto(Colaborador $colaborador): string
    {
        return $colaborador->persona
            ? ObtenerNombrePersona::desde($colaborador->persona)
            : "Colaborador #{$colaborador->id}";
    }

    public function nombreCompletoConCodigo(Colaborador $colaborador): string
    {
        $nombre = $this->obtenerNombreCompleto($colaborador);

        return trim(($colaborador->codigo ?? '').' - '.$nombre);
    }
}

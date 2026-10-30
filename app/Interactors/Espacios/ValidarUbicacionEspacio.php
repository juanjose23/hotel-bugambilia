<?php

declare(strict_types=1);

namespace App\Interactors\Espacios;

use App\BusinessLogic\Espacios\ValidarJerarquiaEspacio;
use App\Repository\Queries\Espacios\ObtenerJerarquiaEspaciosQuery;

final readonly class ValidarUbicacionEspacio
{
    public function __construct(private ObtenerJerarquiaEspaciosQuery $jerarquia, private ValidarJerarquiaEspacio $validar) {}

    public function ejecutar(?int $id, ?int $padreId): void
    {
        $this->validar->validar($id, $padreId, $this->jerarquia->ejecutar());
    }
}

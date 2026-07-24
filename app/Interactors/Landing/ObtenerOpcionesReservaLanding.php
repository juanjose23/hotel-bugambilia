<?php

declare(strict_types=1);

namespace App\Interactors\Landing;

use App\Repository\Queries\Reservas\ObtenerOpcionesReservaPublicaQuery;

final class ObtenerOpcionesReservaLanding
{
    public function __construct(private readonly ObtenerOpcionesReservaPublicaQuery $opciones) {}

    /** @return array<string, array<int, array<string, mixed>>> */
    public function ejecutar(?int $espacioPrincipalId = null): array
    {
        return $this->opciones->obtener($espacioPrincipalId);
    }
}

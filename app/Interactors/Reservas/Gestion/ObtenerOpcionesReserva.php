<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use App\Repository\Queries\Reservas\ObtenerOpcionesReservaPublicaQuery;

final class ObtenerOpcionesReserva
{
    public function __construct(private readonly ObtenerOpcionesReservaPublicaQuery $opciones) {}

    /** @return array<string, array<int, array<string, mixed>>> */
    public function ejecutar(?int $espacioPrincipalId = null): array
    {
        return $this->opciones->obtener($espacioPrincipalId);
    }
}

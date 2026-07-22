<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\Solicitud;

final class VerificarSolicitudBloqueada
{
    public function estaBloqueada(Solicitud $solicitud): bool
    {
        return $solicitud->ordenesCompra()
            ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
            ->exists();
    }
}

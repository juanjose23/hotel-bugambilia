<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\Cotizacion;

final class VerificarEdicionCotizacion
{
    public function puedeEditar(Cotizacion $cotizacion): bool
    {
        return ! $cotizacion->solicitud?->ordenesCompra()
            ->where('estado', '!=', EstadoOrdenCompra::Cancelada)
            ->exists();
    }
}

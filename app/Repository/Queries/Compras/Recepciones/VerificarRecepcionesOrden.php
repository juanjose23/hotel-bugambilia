<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionCompra;

final class VerificarRecepcionesOrden
{
    public function ejecutar(int $ordenId): bool
    {
        return RecepcionCompra::where('orden_compra_id', $ordenId)->exists();
    }
}

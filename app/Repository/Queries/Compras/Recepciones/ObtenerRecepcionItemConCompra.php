<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionItem;

class ObtenerRecepcionItemConCompra
{
    public function ejecutar(int $id): ?RecepcionItem
    {
        return RecepcionItem::with('recepcion.ordenCompra')->find($id);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Recepciones;

use App\Repository\Models\Compras\RecepcionItem;

final class ObtenerRecepcionItemConProducto
{
    public function ejecutar(int $id): RecepcionItem
    {
        return RecepcionItem::query()
            ->with(['producto', 'recepcion'])
            ->findOrFail($id);
    }
}

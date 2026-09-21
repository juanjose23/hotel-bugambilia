<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Repository\Models\Inventario\InventarioFisico;

final class InventarioFisicoRepositorio implements InventarioFisicoRepositorioInterface
{
    public function guardar(InventarioFisico $inventario): void
    {
        $inventario->save();
    }

    public function actualizarEstado(InventarioFisico $inventario, EstadoInventarioFisico $estado): void
    {
        $inventario->update(['estado' => $estado->value]);
    }
}

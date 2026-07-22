<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\InventarioFisico;

interface InventarioFisicoRepositorioInterface
{
    public function guardar(InventarioFisico $inventario): void;
}

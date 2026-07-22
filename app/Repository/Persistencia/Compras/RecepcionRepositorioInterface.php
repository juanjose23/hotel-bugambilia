<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Compras;

use App\Enums\Compras\EstadoRecepcion;
use App\Repository\Models\Compras\RecepcionCompra;

interface RecepcionRepositorioInterface
{
    public function actualizarEstado(RecepcionCompra $recepcion, EstadoRecepcion $estado): void;
}

<?php

declare(strict_types=1);

namespace App\Events\Compras;

use App\Repository\Models\Compras\RecepcionCompra;
use Illuminate\Foundation\Events\Dispatchable;

final class RecepcionCompletada
{
    use Dispatchable;

    public function __construct(
        public readonly RecepcionCompra $recepcion,
    ) {}
}

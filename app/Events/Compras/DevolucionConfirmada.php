<?php

declare(strict_types=1);

namespace App\Events\Compras;

use App\Repository\Models\Compras\DevolucionCompra;
use Illuminate\Foundation\Events\Dispatchable;

final class DevolucionConfirmada
{
    use Dispatchable;

    public function __construct(
        public readonly DevolucionCompra $devolucion,
    ) {}
}

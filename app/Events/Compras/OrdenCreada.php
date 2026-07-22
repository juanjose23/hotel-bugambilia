<?php

declare(strict_types=1);

namespace App\Events\Compras;

use App\Repository\Models\Compras\OrdenCompra;
use Illuminate\Foundation\Events\Dispatchable;

final class OrdenCreada
{
    use Dispatchable;

    public function __construct(
        public readonly OrdenCompra $orden,
    ) {}
}

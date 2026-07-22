<?php

declare(strict_types=1);

namespace App\Events\Compras;

use App\Repository\Models\Compras\Cotizacion;
use Illuminate\Foundation\Events\Dispatchable;

final class CotizacionCreada
{
    use Dispatchable;

    public function __construct(
        public readonly Cotizacion $cotizacion,
    ) {}
}

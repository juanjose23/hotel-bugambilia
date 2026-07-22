<?php

declare(strict_types=1);

namespace App\Events\Compras;

use App\Repository\Models\Compras\Solicitud;
use Illuminate\Foundation\Events\Dispatchable;

final class SolicitudAprobada
{
    use Dispatchable;

    public function __construct(
        public readonly Solicitud $solicitud,
    ) {}
}

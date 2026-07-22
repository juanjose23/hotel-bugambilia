<?php

declare(strict_types=1);

namespace App\Events\Limpieza;

use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class SolicitudLimpiezaCreada
{
    use Dispatchable;

    public function __construct(
        public SolicitudLimpieza $solicitud,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Events\Activos;

use App\Repository\Models\Activos\Activo;
use Illuminate\Foundation\Events\Dispatchable;

class ActivoEnviadoMantenimiento
{
    use Dispatchable;

    public function __construct(
        public readonly Activo $activo,
    ) {}
}

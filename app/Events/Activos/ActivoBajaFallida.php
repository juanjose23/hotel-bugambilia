<?php

declare(strict_types=1);

namespace App\Events\Activos;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

class ActivoBajaFallida
{
    use Dispatchable;

    public function __construct(
        public readonly int $activoId,
        public readonly Throwable $exception,
    ) {}
}

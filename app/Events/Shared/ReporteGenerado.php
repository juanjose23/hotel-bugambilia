<?php

declare(strict_types=1);

namespace App\Events\Shared;

use Illuminate\Foundation\Events\Dispatchable;

final class ReporteGenerado
{
    use Dispatchable;

    public function __construct(
        public readonly int $usuarioId,
        public readonly string $codigoReporte,
        public readonly ?string $urlDescarga = null,
    ) {}
}

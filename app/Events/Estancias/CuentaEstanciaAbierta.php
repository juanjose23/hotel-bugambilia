<?php

declare(strict_types=1);

namespace App\Events\Estancias;

use App\Repository\Models\Estancias\CuentaEstancia;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CuentaEstanciaAbierta
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly CuentaEstancia $cuenta,
    ) {}
}

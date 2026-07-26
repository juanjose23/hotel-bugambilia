<?php

declare(strict_types=1);

namespace App\Events\Cuentas;

use App\Repository\Models\Cuentas\Cuenta;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CuentaAbierta
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Cuenta $cuenta) {}
}

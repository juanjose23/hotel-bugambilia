<?php

declare(strict_types=1);

namespace App\Events\Estancias;

use App\Repository\Models\Estancias\MovimientoCuentaEstancia;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MovimientoCuentaRegistrado implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MovimientoCuentaEstancia $movimiento) {}
}

<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Reservas\Reserva;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ReservaCreada implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Reserva $reserva) {}
}

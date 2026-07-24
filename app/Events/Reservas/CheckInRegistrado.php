<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Estancias\Estancia;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CheckInRegistrado implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Estancia $estancia) {}
}

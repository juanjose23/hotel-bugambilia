<?php

declare(strict_types=1);

namespace App\Jobs\Reservas;

use App\Interactors\Reservas\Gestion\LimpiarReservasNoConfirmadas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class LimpiarReservacionesNoConfirmadasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120];

    public function handle(LimpiarReservasNoConfirmadas $interactor): void
    {
        $interactor->ejecutar();
    }
}

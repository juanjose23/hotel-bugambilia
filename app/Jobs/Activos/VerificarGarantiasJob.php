<?php

declare(strict_types=1);

namespace App\Jobs\Activos;

use App\Interactors\Activos\Mantenimiento\VerificarGarantiasActivos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarGarantiasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(VerificarGarantiasActivos $useCase): void
    {
        $useCase->ejecutar();
    }
}

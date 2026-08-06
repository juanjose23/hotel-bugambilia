<?php

declare(strict_types=1);

namespace App\Jobs\Activos;

use App\Interactors\Activos\Mantenimiento\DetectarMantenimientosPreventivos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarMantenimientosPreventivosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(DetectarMantenimientosPreventivos $useCase): void
    {
        $useCase->ejecutar();
    }
}

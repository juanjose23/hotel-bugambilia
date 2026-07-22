<?php

declare(strict_types=1);

namespace App\Jobs\Activos;

use App\Interactors\Activos\NotificarMantenimientos;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificarMantenimientosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(NotificarMantenimientos $useCase): void
    {
        $useCase->ejecutar();
    }
}

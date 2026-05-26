<?php

declare(strict_types=1);

namespace App\Jobs\Activos;

use App\UseCases\Activos\Mutations\SincronizarEstadoActivo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SincronizarEstadoActivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(SincronizarEstadoActivo $useCase): void
    {
        $useCase->execute();
    }
}

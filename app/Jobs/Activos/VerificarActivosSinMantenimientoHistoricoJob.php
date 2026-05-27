<?php

declare(strict_types=1);

namespace App\Jobs\Activos;

use App\UseCases\Activos\Mutations\VerificarActivosSinMantenimientoHistorico;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerificarActivosSinMantenimientoHistoricoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(VerificarActivosSinMantenimientoHistorico $useCase): void
    {
        $useCase->execute();
    }
}

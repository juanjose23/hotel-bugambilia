<?php

declare(strict_types=1);

namespace App\Jobs\Restaurante;

use App\Interactors\Restaurante\Mesas\ProcesarNoShowsRestaurante;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ProcesarNoShowsRestauranteJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $minutosTolerancia = 30
    ) {}

    public function handle(ProcesarNoShowsRestaurante $interactor): void
    {
        $interactor->ejecutar($this->minutosTolerancia);
    }
}

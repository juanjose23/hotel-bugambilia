<?php

declare(strict_types=1);

namespace App\Events\Reservas;

use App\Repository\Models\Estancias\Estancia;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class EstanciaHabitacionExtendida
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Estancia $estancia,
        public CarbonInterface $anteriorSalida,
        public CarbonInterface $nuevaSalida,
    ) {}
}

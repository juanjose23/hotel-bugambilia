<?php

declare(strict_types=1);

namespace App\Repository\Observers\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class LimpiezaEjecucionObserver
{
    public function saving(LimpiezaEjecucion $model): void {}
}

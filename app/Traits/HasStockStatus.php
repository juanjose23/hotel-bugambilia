<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\Shared\EstadoStock;

trait HasStockStatus
{
    public function getEstadoEnumAttribute(): EstadoStock
    {
        return EstadoStock::calcular(
            (float) $this->cantidad_actual,
            (float) $this->cantidad_ideal
        );
    }
}

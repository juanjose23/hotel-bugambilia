<?php

declare(strict_types=1);

namespace App\Listeners\Activos;

use App\Events\Activos\ActivoBajaFallida;
use Illuminate\Support\Facades\Log;

final class LogFalloBaja
{
    public function handle(ActivoBajaFallida $event): void
    {
        Log::error('Fallo al dar de baja activo', [
            'activo_id' => $event->activoId,
            'error' => $event->exception->getMessage(),
        ]);
    }
}

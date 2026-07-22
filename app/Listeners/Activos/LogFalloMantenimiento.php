<?php

declare(strict_types=1);

namespace App\Listeners\Activos;

use App\Events\Activos\ActivoMantenimientoFallido;
use Illuminate\Support\Facades\Log;

final class LogFalloMantenimiento
{
    public function handle(ActivoMantenimientoFallido $event): void
    {
        Log::error('Fallo al enviar activo a mantenimiento', [
            'activo_id' => $event->activoId,
            'error' => $event->exception->getMessage(),
        ]);
    }
}

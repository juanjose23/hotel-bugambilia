<?php

declare(strict_types=1);

namespace App\Listeners\Reservas;

use App\Events\Reservas\HuespedModificado;
use Illuminate\Support\Facades\Log;

final class AuditarCambioHuesped
{
    public function handle(HuespedModificado $event): void
    {
        Log::info('Hue{accion} modificado', [
            'huesped_id' => $event->huesped->id,
            'accion' => $event->accion,
            'reserva_detalle_id' => $event->huesped->reserva_detalle_id,
            'usuario_id' => auth()->id(),
            'datos_anteriores' => $event->datosAnteriores,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Listeners\Activos;

use App\Events\Activos\ActivoEnviadoMantenimiento;
use Filament\Notifications\Notification;

final class NotificarActivoMantenimiento
{
    public function handle(ActivoEnviadoMantenimiento $event): void
    {
        $nombre = $event->activo->nombre_descriptivo ?? "Activo #{$event->activo->id}";
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        Notification::make()
            ->title('Activo enviado a mantenimiento')
            ->body("El activo \"{$nombre}\" ha sido enviado a taller de mantenimiento.")
            ->success()
            ->sendToDatabase($user);
    }
}

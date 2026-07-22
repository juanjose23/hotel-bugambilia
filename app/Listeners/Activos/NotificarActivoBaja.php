<?php

declare(strict_types=1);

namespace App\Listeners\Activos;

use App\Events\Activos\ActivoDadoDeBaja;
use Filament\Notifications\Notification;

final class NotificarActivoBaja
{
    public function handle(ActivoDadoDeBaja $event): void
    {
        $nombre = $event->activo->nombre_descriptivo ?? "Activo #{$event->activo->id}";
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        Notification::make()
            ->title('Activo dado de baja')
            ->body("El activo \"{$nombre}\" ha sido dado de baja exitosamente.")
            ->success()
            ->sendToDatabase($user);
    }
}

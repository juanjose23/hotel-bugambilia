<?php

declare(strict_types=1);

namespace App\Notifications\Usuarios;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;

final class ClienteRegistradoExitoso extends BaseNotification
{
    use Queueable;

    public function __construct(
        private readonly bool $esNuevo,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        $mensaje = $this->esNuevo
            ? 'Nuevo cliente registrado exitosamente.'
            : 'Cliente vinculado a persona existente.';

        return Notification::make()
            ->title('Cliente registrado')
            ->body($mensaje)
            ->success()
            ->getDatabaseMessage();
    }
}

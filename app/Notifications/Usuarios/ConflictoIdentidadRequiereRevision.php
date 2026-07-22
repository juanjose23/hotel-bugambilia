<?php

declare(strict_types=1);

namespace App\Notifications\Usuarios;

use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification as BaseNotification;

final class ConflictoIdentidadRequiereRevision extends BaseNotification
{
    use Queueable;

    public function __construct(
        private readonly ConflictoIdentidad $conflicto,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return Notification::make()
            ->title('Conflicto de identidad detectado')
            ->body("Se requiere revisión manual para el conflicto #{$this->conflicto->id}.")
            ->warning()
            ->getDatabaseMessage();
    }
}

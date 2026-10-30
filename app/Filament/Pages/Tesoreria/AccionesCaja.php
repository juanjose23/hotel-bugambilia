<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tesoreria;

use App\Repository\Models\User;
use DomainException;
use Filament\Notifications\Notification;

trait AccionesCaja
{
    private function actorId(): int
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            throw new DomainException('Inicia sesión para operar caja.');
        }

        return $user->id;
    }

    private function operar(\Closure $accion, string $mensaje): void
    {
        try {
            $accion();
            Notification::make()->title($mensaje)->success()->send();
        } catch (DomainException $e) {
            Notification::make()->title('Revisa la operación')->body($e->getMessage())->danger()->send();
        }
    }
}

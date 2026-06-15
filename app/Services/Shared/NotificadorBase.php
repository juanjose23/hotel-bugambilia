<?php

declare(strict_types=1);

namespace App\Services\Shared;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

abstract class NotificadorBase
{
    protected function enviar(
        User $user,
        string $title,
        ?string $body = null,
        string $icon = 'heroicon-o-information',
        ?string $url = null,
        string $status = 'info',
        string $actionLabel = 'Ver detalle',
    ): void {
        $notification = Notification::make()
            ->title($title)
            ->icon($icon)
            ->body($body ?? '');

        match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            'danger' => $notification->danger(),
            default => $notification->info(),
        };

        if ($url !== null) {
            $notification->actions([
                Action::make('view')
                    ->label($actionLabel)
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($user);
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function notificarMultiples(
        Collection $users,
        string $title,
        ?string $body = null,
        string $icon = 'heroicon-o-information',
        ?string $url = null,
        string $status = 'info',
        string $actionLabel = 'Ver detalle',
    ): void {
        foreach ($users as $user) {
            $this->enviar($user, $title, $body, $icon, $url, $status, $actionLabel);
        }
    }
}

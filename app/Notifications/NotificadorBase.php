<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Notifications\CanalNotificacion;
use App\Repository\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

abstract class NotificadorBase
{
    protected function make(DatosNotificacion $data): Notification
    {
        $notification = Notification::make()
            ->title($data->title)
            ->body($data->body)
            ->icon($data->type->getIcon())
            ->color($data->type->getColor());

        if ($data->actions !== []) {
            $notification->actions($data->actions);
        }

        return $notification;
    }

    protected function send(User $user, DatosNotificacion $data): void
    {
        $notification = $this->make($data);

        foreach ($data->channels as $channel) {
            match ($channel) {
                CanalNotificacion::BaseDeDatos => $notification->sendToDatabase($user),
                CanalNotificacion::Correo => $this->enviarCorreo($user, $data),
                CanalNotificacion::TiempoReal => $this->enviarBroadcast($user, $data),
            };
        }
    }

    /** @param Collection<int, User> $users */
    protected function sendMany(Collection $users, DatosNotificacion $data): void
    {
        foreach ($users as $user) {
            $this->send($user, $data);
        }
    }

    /** @param User|Collection<int, User> $destinatarios */
    public function enviar(User|Collection $destinatarios, DatosNotificacion $data): void
    {
        if ($destinatarios instanceof User) {
            $this->send($destinatarios, $data);
        } else {
            $this->sendMany($destinatarios, $data);
        }
    }

    protected function enviarCorreo(User $user, DatosNotificacion $data): void {}

    protected function enviarBroadcast(User $user, DatosNotificacion $data): void {}
}

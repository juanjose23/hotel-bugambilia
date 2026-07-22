<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Notifications\CanalNotificacion;
use App\Enums\Notifications\TipoNotificacion;
use Filament\Actions\Action;

class DatosNotificacion
{
    /** @var array<int, Action> */
    public readonly array $actions;

    /** @var array<int, CanalNotificacion> */
    public readonly array $channels;

    /**
     * @param  array<int, Action>  $actions
     * @param  array<int, CanalNotificacion>  $channels
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly TipoNotificacion $type,
        array $actions = [],
        array $channels = [CanalNotificacion::BaseDeDatos],
    ) {
        $this->actions = $actions;
        $this->channels = $channels;
    }
}

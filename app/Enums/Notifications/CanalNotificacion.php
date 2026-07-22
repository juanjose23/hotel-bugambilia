<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum CanalNotificacion: string implements HasLabel
{
    use TieneAyudantesEnum;

    case BaseDeDatos = 'database';
    case Correo = 'mail';
    case TiempoReal = 'broadcast';

    public function getLabel(): string
    {
        return match ($this) {
            self::BaseDeDatos => 'Base de datos',
            self::Correo => 'Correo electrónico',
            self::TiempoReal => 'Tiempo real',
        };
    }

    public function etiqueta(): string
    {
        return $this->getLabel();
    }
}

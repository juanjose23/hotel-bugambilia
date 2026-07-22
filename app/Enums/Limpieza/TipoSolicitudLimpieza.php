<?php

declare(strict_types=1);

namespace App\Enums\Limpieza;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoSolicitudLimpieza: string implements HasLabel
{
    use TieneAyudantesEnum;

    case Programada = 'programada';
    case Express = 'express';
    case Lavandera = 'lavandera';
    case General = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::Programada => 'Programada',
            self::Express => 'Express',
            self::Lavandera => 'Lavandera',
            self::General => 'General',
        };
    }

    public function requiereTurno(): bool
    {
        return in_array($this, [self::Programada, self::General], strict: true);
    }
}

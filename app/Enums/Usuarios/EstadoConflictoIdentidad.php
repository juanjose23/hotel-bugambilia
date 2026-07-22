<?php

declare(strict_types=1);

namespace App\Enums\Usuarios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoConflictoIdentidad: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 'pendiente';
    case Resuelto = 'resuelto';
    case Rechazado = 'rechazado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Resuelto => 'Resuelto',
            self::Rechazado => 'Rechazado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Resuelto => 'success',
            self::Rechazado => 'danger',
        };
    }
}

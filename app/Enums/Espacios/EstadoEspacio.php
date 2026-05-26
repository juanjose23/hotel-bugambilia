<?php

declare(strict_types=1);

namespace App\Enums\Espacios;

enum EstadoEspacio: int
{
    case Inactivo = 0;
    case Activo = 1;
    case Mantenimiento = 2;

    public function label(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Activo => 'Activo',
            self::Mantenimiento => 'Mantenimiento',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Inactivo => 'gray',
            self::Activo => 'success',
            self::Mantenimiento => 'warning',
        };
    }
}

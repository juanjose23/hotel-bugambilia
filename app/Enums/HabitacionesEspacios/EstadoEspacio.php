<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoEspacio: int implements HasColor, HasIcon, HasLabel
{
    case Inactivo = 0;
    case Disponible = 1;
    case Mantenimiento = 2;
    case Limpieza = 3;
    case Reservado = 4;
    case Ocupado = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Disponible => 'Disponible',
            self::Mantenimiento => 'En Mantenimiento',
            self::Limpieza => 'En Limpieza (Mesa Sucia / Por desinfectar)',
            self::Reservado => 'Reservado',
            self::Ocupado => 'Ocupado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Inactivo => 'gray',
            self::Disponible => 'success',
            self::Mantenimiento => 'warning',
            self::Limpieza => 'info',
            self::Reservado => 'primary',
            self::Ocupado => 'danger',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::Inactivo => Heroicon::XCircle,
            self::Disponible => Heroicon::CheckCircle,
            self::Mantenimiento => Heroicon::WrenchScrewdriver,
            self::Limpieza => Heroicon::Sparkles,
            self::Reservado => Heroicon::Calendar,
            self::Ocupado => Heroicon::LockClosed,
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $estado) {
            $options[$estado->value] = $estado->getLabel();
        }

        return $options;
    }
}

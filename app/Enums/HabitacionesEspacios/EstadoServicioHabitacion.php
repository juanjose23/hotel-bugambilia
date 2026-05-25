<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoServicioHabitacion: int implements HasColor, HasIcon, HasLabel
{
    case Inactivo = 0;
    case Activo = 1;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function getIcon(): Heroicon
    {
        return $this->icon();
    }

    public function label(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Activo => 'Activo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Inactivo => 'danger',
            self::Activo => 'success',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Inactivo => Heroicon::XCircle,
            self::Activo => Heroicon::CheckCircle,
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}

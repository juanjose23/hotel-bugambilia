<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoPlanMantenimiento: int implements HasColor, HasIcon, HasLabel
{
    case Activo = 1;
    case Inactivo = 2;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function getIcon(): BackedEnum
    {
        return $this->icon();
    }

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Inactivo => 'Inactivo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::Inactivo => 'danger',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Activo => Heroicon::CheckCircle,
            self::Inactivo => Heroicon::XCircle,
        };
    }
}

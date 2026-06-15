<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoServicioAsignacion: int implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Inactivo = 0;
    case Activo = 1;

    public function getLabel(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Activo => 'Activo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Inactivo => 'danger',
            self::Activo => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Inactivo => Heroicon::XCircle,
            self::Activo => Heroicon::CheckCircle,
        };
    }
}

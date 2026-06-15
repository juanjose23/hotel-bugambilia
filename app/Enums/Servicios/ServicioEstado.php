<?php

declare(strict_types=1);

namespace App\Enums\Servicios;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum ServicioEstado: int implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Activo = 1;
    case EnReparacion = 2;
    case Inactivo = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::EnReparacion => 'En Reparación',
            self::Inactivo => 'Inactivo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::EnReparacion => 'warning',
            self::Inactivo => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Activo => Heroicon::CheckCircle,
            self::EnReparacion => Heroicon::WrenchScrewdriver,
            self::Inactivo => Heroicon::XCircle,
        };
    }
}

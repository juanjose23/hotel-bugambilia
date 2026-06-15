<?php

declare(strict_types=1);

namespace App\Enums\Shared;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum PrecioEstado: int implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Vigente = 1;
    case NoVigente = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::NoVigente => 'No Vigente',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::NoVigente => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Vigente => Heroicon::CheckCircle,
            self::NoVigente => Heroicon::XCircle,
        };
    }
}

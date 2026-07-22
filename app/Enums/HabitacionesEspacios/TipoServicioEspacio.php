<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoServicioEspacio: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Meseros = 'meseros';
    case Autoservicio = 'autoservicio';
    case Mixto = 'mixto';

    public function getLabel(): string
    {
        return match ($this) {
            self::Meseros => 'Meseros',
            self::Autoservicio => 'Autoservicio',
            self::Mixto => 'Mixto',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Meseros => 'primary',
            self::Autoservicio => 'success',
            self::Mixto => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Meseros => Heroicon::UserGroup,
            self::Autoservicio => Heroicon::ShoppingCart,
            self::Mixto => Heroicon::ArrowsRightLeft,
        };
    }
}

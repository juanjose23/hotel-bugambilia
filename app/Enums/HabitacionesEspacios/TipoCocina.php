<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\HasEnumHelpers;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoCocina: string implements HasColor, HasIcon, HasLabel
{
    use HasEnumHelpers;

    case Buffet = 'buffet';
    case ACarta = 'a_la_carta';
    case Mixto = 'mixto';
    case Barra = 'barra';

    public function getLabel(): string
    {
        return match ($this) {
            self::Buffet => 'Buffet',
            self::ACarta => 'A la carta',
            self::Mixto => 'Mixto (Buffet + Carta)',
            self::Barra => 'Barra de tragos / Snacks',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Buffet => 'success',
            self::ACarta => 'warning',
            self::Mixto => 'info',
            self::Barra => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Buffet => Heroicon::Square3Stack3d,
            self::ACarta => Heroicon::DocumentText,
            self::Mixto => Heroicon::AdjustmentsHorizontal,
            self::Barra => Heroicon::Beaker,
        };
    }
}

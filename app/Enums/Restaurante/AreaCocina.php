<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AreaCocina: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case COCINA = 'cocina';
    case BAR = 'bar';
    case POSTRES = 'postres';
    case PARRILLA = 'parrilla';

    public function getLabel(): string
    {
        return match ($this) {
            self::COCINA => 'Cocina Principal',
            self::BAR => 'Barra / Tragos',
            self::POSTRES => 'Postres & Repostería',
            self::PARRILLA => 'Parrilla & Carnes',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::COCINA => 'warning',
            self::BAR => 'info',
            self::POSTRES => 'success',
            self::PARRILLA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::COCINA => 'heroicon-o-cog-6-tooth',
            self::BAR => 'heroicon-o-beaker',
            self::POSTRES => 'heroicon-o-sparkles',
            self::PARRILLA => 'heroicon-o-fire',
        };
    }
}

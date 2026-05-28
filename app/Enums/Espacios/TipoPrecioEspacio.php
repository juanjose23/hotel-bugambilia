<?php

declare(strict_types=1);

namespace App\Enums\Espacios;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoPrecioEspacio: string implements HasColor, HasIcon, HasLabel
{
    case Base = 'base';
    case PorHora = 'por_hora';

    public function getLabel(): string
    {
        return match ($this) {
            self::Base => 'Precio Base / Reserva Completa',
            self::PorHora => 'Tarifa por Hora / Alquiler Horario',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Base => 'primary',
            self::PorHora => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Base => Heroicon::CurrencyDollar,
            self::PorHora => Heroicon::Clock,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}

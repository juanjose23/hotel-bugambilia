<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoEspacio: string implements HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case RESTAURANTE = 'restaurante';
    case AMBIENTE = 'ambiente';
    case TERRAZA = 'terraza';
    case BAR = 'bar';
    case MESA = 'mesa';
    case GYM = 'gym';
    case SALON = 'salon';
    case SPA = 'spa';
    case PISCINA = 'piscina';
    case CANCHA = 'cancha';
    case OTRO = 'otro';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESTAURANTE => 'Restaurante',
            self::AMBIENTE => 'Ambiente / Área',
            self::TERRAZA => 'Terraza',
            self::BAR => 'Bar & Lounge',
            self::MESA => 'Mesa',
            self::GYM => 'Gimnasio',
            self::SALON => 'Salón',
            self::SPA => 'Spa',
            self::PISCINA => 'Piscina',
            self::CANCHA => 'Cancha',
            self::OTRO => 'Otro',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::RESTAURANTE => 'heroicon-o-building-storefront',
            self::AMBIENTE => 'heroicon-o-sun',
            self::TERRAZA => 'heroicon-o-home-modern',
            self::BAR => 'heroicon-o-beaker',
            self::MESA => 'heroicon-o-users',
            self::GYM => 'heroicon-o-wrench-screwdriver',
            self::SALON => 'heroicon-o-star',
            self::SPA => 'heroicon-o-sparkles',
            self::PISCINA => 'heroicon-o-circle-stack',
            self::CANCHA => 'heroicon-o-map',
            self::OTRO => 'heroicon-o-question-mark-circle',
        };
    }
}

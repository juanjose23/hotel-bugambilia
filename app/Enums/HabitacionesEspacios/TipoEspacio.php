<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoEspacio: string implements HasIcon, HasLabel
{
    case RESTAURANTE = 'restaurante';
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
            self::RESTAURANTE => 'Restaurante / Bar',
            self::MESA => 'Mesa de Restaurante/Bar',
            self::GYM => 'Gimnasio / Área de Fitness',
            self::SALON => 'Salón / Sala de Eventos',
            self::SPA => 'Cabina de Spa / Masajes',
            self::PISCINA => 'Área de Piscina / Camastro',
            self::CANCHA => 'Cancha Deportiva',
            self::OTRO => 'Otro Espacio',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::RESTAURANTE => Heroicon::BuildingStorefront,
            self::MESA => Heroicon::TableCells,
            self::GYM => Heroicon::Trophy,
            self::SALON => Heroicon::PresentationChartBar,
            self::SPA => Heroicon::Sparkles,
            self::PISCINA => Heroicon::Sun,
            self::CANCHA => Heroicon::Flag,
            self::OTRO => Heroicon::MapPin,
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $tipo) {
            $options[$tipo->value] = $tipo->getLabel();
        }

        return $options;
    }
}

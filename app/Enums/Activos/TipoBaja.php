<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoBaja: string implements HasColor, HasIcon, HasLabel
{
    case Obsolescencia = 'obsolescencia';
    case DanoIrreparable = 'daño_irreparable';
    case Robo = 'robo';
    case Perdida = 'perdida';
    case Donacion = 'donacion';
    case Venta = 'venta';

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
            self::Obsolescencia => 'Obsolescencia',
            self::DanoIrreparable => 'Daño irreparable',
            self::Robo => 'Robo',
            self::Perdida => 'Pérdida / Extravío',
            self::Donacion => 'Donación',
            self::Venta => 'Venta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Obsolescencia => 'gray',
            self::DanoIrreparable => 'danger',
            self::Robo => 'danger',
            self::Perdida => 'danger',
            self::Donacion => 'info',
            self::Venta => 'success',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Obsolescencia => Heroicon::ArchiveBox,
            self::DanoIrreparable => Heroicon::ExclamationTriangle,
            self::Robo => Heroicon::LockOpen,
            self::Perdida => Heroicon::QuestionMarkCircle,
            self::Donacion => Heroicon::Gift,
            self::Venta => Heroicon::CurrencyDollar,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoOrdenCompra: int implements HasColor, HasIcon, HasLabel
{
    case Borrador = 1;
    case Emitida = 2;
    case EnTransito = 3;
    case Recibida = 4;
    case Cancelada = 5;
    case DevueltaParcialmente = 6;
    case DevueltaTotalmente = 7;
    case Parcial = 8;

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
            self::Borrador => 'Borrador',
            self::Emitida => 'Emitida',
            self::EnTransito => 'En Tránsito',
            self::Recibida => 'Recibida',
            self::Cancelada => 'Cancelada',
            self::DevueltaParcialmente => 'Devuelta Parcialmente',
            self::DevueltaTotalmente => 'Devuelta Totalmente',
            self::Parcial => 'Parcialmente Recibida',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Emitida => 'info',
            self::EnTransito => 'warning',
            self::Recibida => 'success',
            self::Cancelada => 'danger',
            self::DevueltaParcialmente => 'warning',
            self::DevueltaTotalmente => 'danger',
            self::Parcial => 'warning',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Borrador => Heroicon::DocumentPlus,
            self::Emitida => Heroicon::PaperAirplane,
            self::EnTransito => Heroicon::Truck,
            self::Recibida => Heroicon::CheckBadge,
            self::Cancelada => Heroicon::XMark,
            self::DevueltaParcialmente => Heroicon::ArrowUturnLeft,
            self::DevueltaTotalmente => Heroicon::ArrowUturnLeft,
            self::Parcial => Heroicon::ArrowPath,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoActivo: int implements HasColor, HasIcon, HasLabel
{
    case Activo = 1;
    case EnMantenimiento = 2;
    case DadoDeBaja = 3;
    case Extraviado = 4;
    case EnTransito = 5;

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
            self::Activo => 'Activo',
            self::EnMantenimiento => 'En mantenimiento',
            self::DadoDeBaja => 'Dado de baja',
            self::Extraviado => 'Extraviado',
            self::EnTransito => 'En tránsito',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::EnMantenimiento => 'warning',
            self::DadoDeBaja => 'danger',
            self::Extraviado => 'danger',
            self::EnTransito => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Activo => Heroicon::CheckCircle,
            self::EnMantenimiento => Heroicon::Wrench,
            self::DadoDeBaja => Heroicon::Trash,
            self::Extraviado => Heroicon::QuestionMarkCircle,
            self::EnTransito => Heroicon::Truck,
        };
    }
}

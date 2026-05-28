<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoStock: int implements HasColor, HasIcon, HasLabel
{
    case Completo = 1;
    case Faltante = 2;
    case Sobrante = 3;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Completo => 'Completo',
            self::Faltante => 'Faltante',
            self::Sobrante => 'Sobrante',
        };
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function color(): string
    {
        return match ($this) {
            self::Completo => 'success',
            self::Faltante => 'danger',
            self::Sobrante => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Completo => 'heroicon-o-check-circle',
            self::Faltante => 'heroicon-o-exclamation-triangle',
            self::Sobrante => 'heroicon-o-arrow-up-circle',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Completo => Heroicon::CheckCircle,
            self::Faltante => Heroicon::ExclamationTriangle,
            self::Sobrante => Heroicon::ArrowUpCircle,
        };
    }

    /** @return array<int, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $estado) {
            $options[$estado->value] = $estado->label();
        }

        return $options;
    }

    public static function calcular(float $actual, float $ideal): self
    {
        if ($actual > $ideal) {
            return self::Sobrante;
        }
        if ($actual < $ideal) {
            return self::Faltante;
        }

        return self::Completo;
    }
}

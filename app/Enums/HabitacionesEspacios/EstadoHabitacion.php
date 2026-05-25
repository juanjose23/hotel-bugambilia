<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoHabitacion: int implements HasColor, HasIcon, HasLabel
{
    case Inactiva = 0;
    case Activa = 1;
    case Mantenimiento = 2;
    case Limpieza = 3;
    case Reserva = 4;
    case Ocupada = 5;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Inactiva => 'Inactiva',
            self::Activa => 'Activa',
            self::Mantenimiento => 'Mantenimiento',
            self::Limpieza => 'Limpieza',
            self::Reserva => 'Reserva',
            self::Ocupada => 'Ocupada',
        };
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function color(): string
    {
        return match ($this) {
            self::Inactiva => 'gray',
            self::Activa => 'success',
            self::Mantenimiento => 'warning',
            self::Limpieza => 'info',
            self::Reserva => 'primary',
            self::Ocupada => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Inactiva => 'heroicon-o-x-circle',
            self::Activa => 'heroicon-o-check-circle',
            self::Mantenimiento => 'heroicon-o-wrench-screwdriver',
            self::Limpieza => 'heroicon-o-sparkles',
            self::Reserva => 'heroicon-o-calendar',
            self::Ocupada => 'heroicon-o-lock-closed',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Inactiva => Heroicon::XCircle,
            self::Activa => Heroicon::CheckCircle,
            self::Mantenimiento => Heroicon::WrenchScrewdriver,
            self::Limpieza => Heroicon::Sparkles,
            self::Reserva => Heroicon::Calendar,
            self::Ocupada => Heroicon::LockClosed,
        };
    }

    /** @return array<int,string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $estado) {
            $options[$estado->value] = $estado->label();
        }

        return $options;
    }

    public static function fromValue(self|int|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom((int) $value);
    }

    public static function labelFor(self|int|string|null $value): string
    {
        return self::fromValue($value)?->label() ?? 'No definido';
    }

    public static function colorFor(self|int|string|null $value): string
    {
        return self::fromValue($value)?->color() ?? 'gray';
    }

    public static function iconFor(self|int|string|null $value): ?Heroicon
    {
        return self::fromValue($value)?->icon();
    }
}

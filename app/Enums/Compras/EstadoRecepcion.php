<?php

namespace App\Enums\Compras;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoRecepcion: int implements HasColor, HasIcon, HasLabel
{
    case Pendiente = 0;
    case Completa = 1;
    case Parcial = 2;
    case ConDiscrepancia = 3;
    case Rechazada = 4;
    case EnCuarentena = 5;

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
            self::Pendiente => 'Pendiente',
            self::Completa => 'Completa',
            self::Parcial => 'Parcial',
            self::ConDiscrepancia => 'Con Discrepancia',
            self::Rechazada => 'Rechazada',
            self::EnCuarentena => 'En Cuarentena',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Completa => 'success',
            self::Parcial => 'warning',
            self::ConDiscrepancia => 'orange',
            self::Rechazada => 'danger',
            self::EnCuarentena => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Pendiente => Heroicon::Clock,
            self::Completa => Heroicon::CheckBadge,
            self::Parcial => Heroicon::ArrowPath,
            self::ConDiscrepancia => Heroicon::ExclamationTriangle,
            self::Rechazada => Heroicon::NoSymbol,
            self::EnCuarentena => Heroicon::ShieldCheck,
        };
    }

    /** @return array<int, self> */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Pendiente => [self::Completa, self::Parcial, self::Rechazada],
            self::Parcial => [self::Completa],
            default => [],
        };
    }

    public function transicionPermitida(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }

    public function esEstadoTerminal(): bool
    {
        return match ($this) {
            self::Completa, self::Rechazada => true,
            default => false,
        };
    }
}

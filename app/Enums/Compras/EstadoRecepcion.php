<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoRecepcion: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 0;
    case Completa = 1;
    case Parcial = 2;
    case ConDiscrepancia = 3;
    case EnCuarentena = 4;
    case Rechazada = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Completa => 'Completa',
            self::Parcial => 'Parcial',
            self::ConDiscrepancia => 'Con Discrepancia',
            self::EnCuarentena => 'En Cuarentena',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'warning',
            self::Completa => 'success',
            self::Parcial => 'warning',
            self::ConDiscrepancia => 'danger',
            self::EnCuarentena => 'danger',
            self::Rechazada => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pendiente => 'heroicon-o-clock',
            self::Completa => 'heroicon-o-check-circle',
            self::Parcial => 'heroicon-o-clipboard-document-check',
            self::ConDiscrepancia => 'heroicon-o-exclamation-triangle',
            self::EnCuarentena => 'heroicon-o-shield-exclamation',
            self::Rechazada => 'heroicon-o-x-circle',
        };
    }
}

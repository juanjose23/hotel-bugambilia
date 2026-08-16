<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoCargo: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Impuesto = 1;
    case Servicio = 2;
    case Propina = 3;
    case Descuento = 4;
    case Recargo = 5;
    case Otro = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Impuesto => 'Impuesto',
            self::Servicio => 'Cargo por Servicio',
            self::Propina => 'Propina',
            self::Descuento => 'Descuento',
            self::Recargo => 'Recargo',
            self::Otro => 'Otro',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Impuesto => 'info',
            self::Servicio => 'primary',
            self::Propina => 'success',
            self::Descuento => 'warning',
            self::Recargo => 'danger',
            self::Otro => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Impuesto => 'heroicon-o-document-text',
            self::Servicio => 'heroicon-o-check-badge',
            self::Propina => 'heroicon-o-currency-dollar',
            self::Descuento => 'heroicon-o-tag',
            self::Recargo => 'heroicon-o-exclamation-triangle',
            self::Otro => 'heroicon-o-question-mark-circle',
        };
    }

    /** Indica si el cargo suma al total (true) o lo reduce (false) */
    public function sumaAlTotal(): bool
    {
        return $this !== self::Descuento;
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoPago: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 'pendiente';
    case APLICADO = 'aplicado';
    case ANULADO = 'anulado';
    case REEMBOLSADO = 'reembolsado';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::APLICADO => 'Aplicado',
            self::ANULADO => 'Anulado',
            self::REEMBOLSADO => 'Reembolsado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::APLICADO => 'success',
            self::ANULADO => 'danger',
            self::REEMBOLSADO => 'gray',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::APLICADO => 'heroicon-o-check-circle',
            self::ANULADO => 'heroicon-o-x-circle',
            self::REEMBOLSADO => 'heroicon-o-arrow-path',
        };
    }
}

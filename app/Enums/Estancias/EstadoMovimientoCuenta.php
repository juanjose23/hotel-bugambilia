<?php

declare(strict_types=1);

namespace App\Enums\Estancias;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoMovimientoCuenta: string implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 'pendiente';
    case CONFIRMADO = 'confirmado';
    case ANULADO = 'anulado';
    case FACTURADO = 'facturado';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::CONFIRMADO => 'Confirmado',
            self::ANULADO => 'Anulado',
            self::FACTURADO => 'Facturado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::CONFIRMADO => 'success',
            self::ANULADO => 'danger',
            self::FACTURADO => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::CONFIRMADO => 'heroicon-o-check-circle',
            self::ANULADO => 'heroicon-o-x-circle',
            self::FACTURADO => 'heroicon-o-document-text',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Inventario;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoLote: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Agotado = 0;
    case Disponible = 1;
    case Cuarentena = 2;
    case Vencido = 3;
    case Rechazado = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Agotado => 'Agotado',
            self::Disponible => 'Disponible',
            self::Cuarentena => 'En Cuarentena',
            self::Vencido => 'Vencido',
            self::Rechazado => 'Rechazado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Agotado => 'gray',
            self::Disponible => 'success',
            self::Cuarentena => 'warning',
            self::Vencido, self::Rechazado => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Agotado => 'heroicon-o-archive-box',
            self::Disponible => 'heroicon-o-check-circle',
            self::Cuarentena => 'heroicon-o-shield-exclamation',
            self::Vencido => 'heroicon-o-calendar-days',
            self::Rechazado => 'heroicon-o-x-circle',
        };
    }
}

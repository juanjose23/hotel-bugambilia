<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoPedido: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case ABIERTO = 1;
    case EN_PREPARACION = 2;
    case LISTO = 3;
    case SERVIDO = 4;
    case PAGADO = 5;
    case CARGADO_A_HABITACION = 6;
    case CANCELADO = 7;

    public function getLabel(): string
    {
        return match ($this) {
            self::ABIERTO => 'Abierto',
            self::EN_PREPARACION => 'En Preparación',
            self::LISTO => 'Listo',
            self::SERVIDO => 'Servido',
            self::PAGADO => 'Pagado',
            self::CARGADO_A_HABITACION => 'Cargado a Habitación',
            self::CANCELADO => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ABIERTO => 'warning',
            self::EN_PREPARACION => 'info',
            self::LISTO, self::PAGADO => 'success',
            self::SERVIDO => 'primary',
            self::CARGADO_A_HABITACION => 'purple',
            self::CANCELADO => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ABIERTO => 'heroicon-o-folder-open',
            self::EN_PREPARACION => 'heroicon-o-fire',
            self::LISTO => 'heroicon-o-check',
            self::SERVIDO => 'heroicon-o-check-circle',
            self::PAGADO => 'heroicon-o-banknotes',
            self::CARGADO_A_HABITACION => 'heroicon-o-home',
            self::CANCELADO => 'heroicon-o-x-circle',
        };
    }

    public static function resolveLabel(mixed $state): string
    {
        if ($state instanceof self) {
            return $state->getLabel();
        }

        if (is_numeric($state) || is_string($state)) {
            return self::tryFrom((int) $state)?->getLabel() ?? (string) $state;
        }

        return '';
    }

    public static function resolveColor(mixed $state): string
    {
        if ($state instanceof self) {
            return $state->getColor();
        }

        if (is_numeric($state) || is_string($state)) {
            return self::tryFrom((int) $state)?->getColor() ?? 'gray';
        }

        return 'gray';
    }
}

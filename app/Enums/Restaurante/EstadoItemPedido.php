<?php

declare(strict_types=1);

namespace App\Enums\Restaurante;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoItemPedido: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case PENDIENTE = 1;
    case EN_PREPARACION = 2;
    case LISTO = 3;
    case SERVIDO = 4;
    case ANULADO = 5;
    case BLOQUEADO_STOCK = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDIENTE => 'Pendiente',
            self::EN_PREPARACION => 'En Preparación',
            self::LISTO => 'Listo para Servir',
            self::SERVIDO => 'Servido',
            self::ANULADO => 'Anulado',
            self::BLOQUEADO_STOCK => 'Bloqueado por Stock',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDIENTE => 'warning',
            self::EN_PREPARACION => 'info',
            self::LISTO => 'success',
            self::SERVIDO => 'primary',
            self::ANULADO => 'danger',
            self::BLOQUEADO_STOCK => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PENDIENTE => 'heroicon-o-clock',
            self::EN_PREPARACION => 'heroicon-o-fire',
            self::LISTO => 'heroicon-o-check',
            self::SERVIDO => 'heroicon-o-hand-thumb-up',
            self::ANULADO => 'heroicon-o-x-mark',
            self::BLOQUEADO_STOCK => 'heroicon-o-exclamation-triangle',
        };
    }
}

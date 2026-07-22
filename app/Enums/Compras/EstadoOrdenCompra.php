<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoOrdenCompra: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 1;
    case Emitida = 2;
    case EnTransito = 3;
    case Recibida = 4;
    case Cancelada = 5;
    case Parcial = 6;
    case Vencida = 7;
    case Rechazada = 8;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Emitida => 'Emitida',
            self::EnTransito => 'En Tránsito',
            self::Recibida => 'Recibida',
            self::Cancelada => 'Cancelada',
            self::Parcial => 'Recibida Parcial',
            self::Vencida => 'Vencida',
            self::Rechazada => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Emitida => 'info',
            self::EnTransito => 'warning',
            self::Recibida => 'success',
            self::Cancelada => 'danger',
            self::Parcial => 'warning',
            self::Vencida => 'danger',
            self::Rechazada => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Borrador => 'heroicon-o-document-text',
            self::Emitida => 'heroicon-o-paper-airplane',
            self::EnTransito => 'heroicon-o-truck',
            self::Recibida => 'heroicon-o-check-circle',
            self::Cancelada => 'heroicon-o-trash',
            self::Parcial => 'heroicon-o-clipboard-document-check',
            self::Vencida => 'heroicon-o-calendar-days',
            self::Rechazada => 'heroicon-o-x-circle',
        };
    }
}

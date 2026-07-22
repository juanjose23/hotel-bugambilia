<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoProcesoCompra: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 1;
    case Pendiente = 2;
    case Aprobado = 3;
    case Rechazado = 4;
    case Cancelado = 5;
    case AceptadoParcial = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador / Activa',
            self::Pendiente => 'Pendiente de Revisión',
            self::Aprobado => 'Aprobado / Aceptado',
            self::Rechazado => 'Rechazado',
            self::Cancelado => 'Cancelado',
            self::AceptadoParcial => 'Aceptado Parcial',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Pendiente => 'warning',
            self::Aprobado => 'success',
            self::Rechazado => 'danger',
            self::Cancelado => 'danger',
            self::AceptadoParcial => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Borrador => 'heroicon-o-pencil-square',
            self::Pendiente => 'heroicon-o-clock',
            self::Aprobado => 'heroicon-o-check-circle',
            self::Rechazado => 'heroicon-o-x-circle',
            self::Cancelado => 'heroicon-o-trash',
            self::AceptadoParcial => 'heroicon-o-document-check',
        };
    }
}

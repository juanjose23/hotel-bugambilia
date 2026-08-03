<?php

declare(strict_types=1);

namespace App\Enums\HabitacionesEspacios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoEspacio: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Inactivo = 0;
    case Disponible = 1;
    case Mantenimiento = 2;
    case Limpieza = 3;
    case Reservado = 4;
    case Ocupado = 5;
    case Sucio = 6;

    public const Inactiva = self::Inactivo;

    public const Activa = self::Disponible;

    public const EN_LIMPIEZA = self::Limpieza;

    public const Reserva = self::Reservado;

    public const Ocupada = self::Ocupado;

    public const SUCIA = self::Sucio;

    public const DISPONIBLE = self::Disponible;

    public function getLabel(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Disponible => 'Disponible',
            self::Mantenimiento => 'Mantenimiento',
            self::Limpieza => 'Limpieza',
            self::Reservado => 'Reservado',
            self::Ocupado => 'Ocupado',
            self::Sucio => 'Sucio',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Inactivo => 'danger',
            self::Disponible => 'success',
            self::Mantenimiento => 'warning',
            self::Limpieza => 'info',
            self::Reservado => 'primary',
            self::Ocupado => 'primary',
            self::Sucio => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Inactivo => 'heroicon-o-x-circle',
            self::Disponible => 'heroicon-o-check-circle',
            self::Mantenimiento => 'heroicon-o-wrench',
            self::Limpieza => 'heroicon-o-sparkles',
            self::Reservado => 'heroicon-o-calendar',
            self::Ocupado => 'heroicon-o-user',
            self::Sucio => 'heroicon-o-exclamation-triangle',
        };
    }
}

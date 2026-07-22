<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoActivo: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Activo = 1;
    case EnMantenimiento = 2;
    case DadoDeBaja = 3;
    case Extraviado = 4;
    case EnTransito = 5;
    case Repuesto = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::EnMantenimiento => 'En Mantenimiento',
            self::DadoDeBaja => 'Dado de Baja',
            self::Extraviado => 'Extraviado',
            self::EnTransito => 'En Tránsito',
            self::Repuesto => 'Para Repuestos (Canibalizado)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Activo => 'success',
            self::EnMantenimiento => 'warning',
            self::DadoDeBaja => 'danger',
            self::Extraviado => 'danger',
            self::EnTransito => 'info',
            self::Repuesto => 'gray',
        };
    }
}

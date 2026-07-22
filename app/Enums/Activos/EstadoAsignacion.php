<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoAsignacion: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Vigente = 1;
    case Cerrada = 2;
    case EnTransito = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Vigente => 'Vigente',
            self::Cerrada => 'Cerrada',
            self::EnTransito => 'En Tránsito',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Vigente => 'success',
            self::Cerrada => 'danger',
            self::EnTransito => 'info',
        };
    }
}

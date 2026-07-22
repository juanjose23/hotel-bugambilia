<?php

declare(strict_types=1);

namespace App\Enums\Catalogos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoProducto: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Perecedero = 1;
    case NoPerecedero = 2;
    case ActivoFijo = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Perecedero => 'Perecedero',
            self::NoPerecedero => 'No Perecedero',
            self::ActivoFijo => 'Activo Fijo (Individualizable)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Perecedero => 'danger',
            self::NoPerecedero => 'success',
            self::ActivoFijo => 'info',
        };
    }
}

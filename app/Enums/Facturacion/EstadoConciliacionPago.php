<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoConciliacionPago: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 1;
    case Conciliada = 2;
    case Diferencia = 3;
    case Ignorada = 4;
    case Reembolsada = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Conciliada => 'Conciliada',
            self::Diferencia => 'Con diferencia',
            self::Ignorada => 'Ignorada',
            self::Reembolsada => 'Reembolsada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Conciliada => 'success',
            self::Diferencia => 'warning',
            self::Ignorada => 'gray',
            self::Reembolsada => 'danger',
        };
    }
}

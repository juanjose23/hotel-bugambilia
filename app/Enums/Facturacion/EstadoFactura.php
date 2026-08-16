<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoFactura: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 1;
    case Emitida = 2;
    case Anulada = 3;
    case Ajustada = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Emitida => 'Emitida',
            self::Anulada => 'Anulada',
            self::Ajustada => 'Ajustada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Emitida => 'success',
            self::Anulada => 'danger',
            self::Ajustada => 'warning',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoFolioFactura: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Reservado = 1;
    case Emitido = 2;
    case Anulado = 3;
    case Fallido = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Reservado => 'Reservado',
            self::Emitido => 'Emitido',
            self::Anulado => 'Anulado',
            self::Fallido => 'Fallido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reservado => 'gray',
            self::Emitido => 'success',
            self::Anulado => 'danger',
            self::Fallido => 'warning',
        };
    }
}

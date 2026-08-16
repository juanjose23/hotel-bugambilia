<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoTransaccionPago: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Pendiente = 1;
    case Autorizada = 2;
    case Capturada = 3;
    case Fallida = 4;
    case Reembolsada = 5;
    case Contracargo = 6;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Autorizada => 'Autorizada',
            self::Capturada => 'Capturada',
            self::Fallida => 'Fallida',
            self::Reembolsada => 'Reembolsada',
            self::Contracargo => 'Contracargo',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendiente => 'gray',
            self::Autorizada => 'info',
            self::Capturada => 'success',
            self::Fallida, self::Contracargo => 'danger',
            self::Reembolsada => 'warning',
        };
    }
}

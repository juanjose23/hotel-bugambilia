<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EstadoVenta: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Emitida = 1;
    case Anulada = 2;
    case NotaCredito = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Emitida => 'Emitida',
            self::Anulada => 'Anulada',
            self::NotaCredito => 'Nota de Crédito',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Emitida => 'success',
            self::Anulada => 'danger',
            self::NotaCredito => 'warning',
        };
    }
}

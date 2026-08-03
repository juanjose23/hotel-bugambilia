<?php

declare(strict_types=1);

namespace App\Enums\Cuentas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BaseCalculo: int implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case SubtotalBruto = 1;
    case SubtotalNeto = 2;
    case TotalConImpuestos = 3;
    case BaseManual = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::SubtotalBruto => 'Subtotal Bruto',
            self::SubtotalNeto => 'Subtotal Neto (después de descuentos)',
            self::TotalConImpuestos => 'Total con Impuestos',
            self::BaseManual => 'Base Manual',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SubtotalBruto => 'info',
            self::SubtotalNeto => 'primary',
            self::TotalConImpuestos => 'success',
            self::BaseManual => 'warning',
        };
    }
}

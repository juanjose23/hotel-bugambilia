<?php

declare(strict_types=1);

namespace App\Enums\Facturacion;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoFactura: int implements HasLabel
{
    use TieneAyudantesEnum;

    case Contado = 1;
    case Credito = 2;
    case Proforma = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Contado => 'Contado',
            self::Credito => 'Credito',
            self::Proforma => 'Proforma',
        };
    }
}

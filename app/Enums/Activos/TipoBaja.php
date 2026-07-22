<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoBaja: string implements HasLabel
{
    use TieneAyudantesEnum;

    case Obsolescencia = 'obsolescencia';
    case DanioIrreparable = 'daño_irreparable';
    case Robo = 'robo';
    case Perdida = 'perdida';
    case Donacion = 'donacion';
    case Venta = 'venta';

    public function getLabel(): string
    {
        return match ($this) {
            self::Obsolescencia => 'Obsolescencia',
            self::DanioIrreparable => 'Daño Irreparable',
            self::Robo => 'Robo',
            self::Perdida => 'Pérdida',
            self::Donacion => 'Donación',
            self::Venta => 'Venta',
        };
    }
}

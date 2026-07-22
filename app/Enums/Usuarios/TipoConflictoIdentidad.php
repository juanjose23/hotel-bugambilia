<?php

declare(strict_types=1);

namespace App\Enums\Usuarios;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TipoConflictoIdentidad: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case Homonimia = 'homonimia';
    case DatosDivergentes = 'datos_divergentes';
    case IdentidadDudosa = 'identidad_dudosa';

    public function getLabel(): string
    {
        return match ($this) {
            self::Homonimia => 'Homonimia',
            self::DatosDivergentes => 'Datos Divergentes',
            self::IdentidadDudosa => 'Identidad Dudosa',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Homonimia => 'warning',
            self::DatosDivergentes => 'info',
            self::IdentidadDudosa => 'danger',
        };
    }
}

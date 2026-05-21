<?php

declare(strict_types=1);

namespace App\Enums\Inventario;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoInventarioFisico: string implements HasColor, HasIcon, HasLabel
{
    case Borrador = 'borrador';
    case Procesado = 'procesado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Procesado => 'Procesado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Procesado => 'success',
        };
    }

    public function getIcon(): BackedEnum
    {
        return match ($this) {
            self::Borrador => Heroicon::PencilSquare,
            self::Procesado => Heroicon::CheckCircle,
        };
    }
}

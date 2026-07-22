<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoDevolucion: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Borrador = 0;
    case Confirmada = 1;
    case Cancelada = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Confirmada => 'Confirmada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Confirmada => 'success',
            self::Cancelada => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Borrador => 'heroicon-o-document-text',
            self::Confirmada => 'heroicon-o-check-circle',
            self::Cancelada => 'heroicon-o-x-circle',
        };
    }
}

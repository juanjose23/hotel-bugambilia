<?php

declare(strict_types=1);

namespace App\Enums\Compras;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoDevolucion: int implements HasColor, HasIcon, HasLabel
{
    case Borrador = 0;
    case Emitida = 1;
    case Confirmada = 2;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function getIcon(): BackedEnum
    {
        return $this->icon();
    }

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Emitida => 'Emitida',
            self::Confirmada => 'Confirmada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Borrador => 'gray',
            self::Emitida => 'warning',
            self::Confirmada => 'success',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Borrador => Heroicon::DocumentText,
            self::Emitida => Heroicon::PaperAirplane,
            self::Confirmada => Heroicon::CheckCircle,
        };
    }

    /** @return array<int, self> */
    public function transicionesPermitidas(): array
    {
        return match ($this) {
            self::Borrador => [self::Emitida, self::Confirmada],
            self::Emitida => [self::Confirmada],
            self::Confirmada => [],
        };
    }

    public function transicionPermitida(self $destino): bool
    {
        return in_array($destino, $this->transicionesPermitidas(), true);
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Inventario;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoLote: int implements HasColor, HasIcon, HasLabel
{
    case Agotado = 0;
    case Disponible = 1;
    case Cuarentena = 2;
    case Vencido = 3;
    case Rechazado = 4;

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return match ($this) {
            self::Agotado => 'Agotado',
            self::Disponible => 'Disponible',
            self::Cuarentena => 'En Cuarentena',
            self::Vencido => 'Vencido',
            self::Rechazado => 'Rechazado / Desperdicio',
        };
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function color(): string
    {
        return match ($this) {
            self::Agotado => 'gray',
            self::Disponible => 'success',
            self::Cuarentena => 'warning',
            self::Vencido => 'danger',
            self::Rechazado => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Agotado => 'heroicon-o-archive-box',
            self::Disponible => 'heroicon-o-check-circle',
            self::Cuarentena => 'heroicon-o-shield-exclamation',
            self::Vencido => 'heroicon-o-x-circle',
            self::Rechazado => 'heroicon-o-no-symbol',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Agotado => Heroicon::ArchiveBox,
            self::Disponible => Heroicon::CheckCircle,
            self::Cuarentena => Heroicon::ShieldExclamation,
            self::Vencido => Heroicon::XCircle,
            self::Rechazado => Heroicon::NoSymbol,
        };
    }

    /**
     * @return array<int,string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $estado) {
            $options[$estado->value] = $estado->label();
        }

        return $options;
    }
}

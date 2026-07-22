<?php

declare(strict_types=1);

namespace App\Enums\Shared;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum EstadoGeneral: int implements HasColor, HasIcon, HasLabel
{
    use TieneAyudantesEnum;

    case Inactivo = 0;
    case Activo = 1;
    case Vencido = 2;

    /** @return array<int, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
            ->all();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Inactivo => 'Inactivo',
            self::Activo => 'Activo',
            self::Vencido => 'Vencido',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Inactivo => 'danger',
            self::Activo => 'success',
            self::Vencido => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Inactivo => Heroicon::XCircle,
            self::Activo => Heroicon::CheckCircle,
            self::Vencido => Heroicon::Clock,
        };
    }
}

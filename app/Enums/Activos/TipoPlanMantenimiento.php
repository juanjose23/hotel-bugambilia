<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoPlanMantenimiento: string implements HasColor, HasIcon, HasLabel
{
    case Preventivo = 'preventivo';
    case Inspeccion = 'inspeccion';

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
            self::Preventivo => 'Preventivo',
            self::Inspeccion => 'Inspección',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Preventivo => 'success',
            self::Inspeccion => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Preventivo => Heroicon::ClipboardDocumentCheck,
            self::Inspeccion => Heroicon::Eye,
        };
    }
}

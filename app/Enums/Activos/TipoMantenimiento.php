<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TipoMantenimiento: string implements HasColor, HasIcon, HasLabel
{
    case Preventivo = 'preventivo';
    case Correctivo = 'correctivo';
    case Garantia = 'garantia';
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
            self::Correctivo => 'Correctivo',
            self::Garantia => 'Garantía',
            self::Inspeccion => 'Inspección',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Preventivo => 'success',
            self::Correctivo => 'danger',
            self::Garantia => 'warning',
            self::Inspeccion => 'info',
        };
    }

    public function icon(): Heroicon
    {
        return match ($this) {
            self::Preventivo => Heroicon::ClipboardDocumentCheck,
            self::Correctivo => Heroicon::Wrench,
            self::Garantia => Heroicon::ShieldCheck,
            self::Inspeccion => Heroicon::Eye,
        };
    }
}

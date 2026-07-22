<?php

declare(strict_types=1);

namespace App\Enums\Activos;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoMantenimiento: string implements HasLabel
{
    use TieneAyudantesEnum;

    case Preventivo = 'preventivo';
    case Correctivo = 'correctivo';
    case Garantia = 'garantia';
    case Inspeccion = 'inspeccion';

    public function getLabel(): string
    {
        return match ($this) {
            self::Preventivo => 'Preventivo',
            self::Correctivo => 'Correctivo',
            self::Garantia => 'Garantía',
            self::Inspeccion => 'Inspección',
        };
    }
}

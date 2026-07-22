<?php

declare(strict_types=1);

namespace App\Enums\Personas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Sexo: string implements HasColor, HasLabel
{
    use TieneAyudantesEnum;

    case MASCULINO = 'M';
    case FEMENINO = 'F';
    case OTRO = 'O';

    public function getLabel(): string
    {
        return match ($this) {
            self::MASCULINO => 'Masculino',
            self::FEMENINO => 'Femenino',
            self::OTRO => 'Otro',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MASCULINO => 'success',
            self::FEMENINO => 'primary',
            self::OTRO => 'danger',
        };
    }
}

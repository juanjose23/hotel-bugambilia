<?php

declare(strict_types=1);

namespace App\Enums\Personas;

use App\Enums\Concerns\TieneAyudantesEnum;
use Filament\Support\Contracts\HasLabel;

enum TipoSangre: string implements HasLabel
{
    use TieneAyudantesEnum;

    case O_POSITIVO = 'O+';
    case O_NEGATIVO = 'O-';
    case A_POSITIVO = 'A+';
    case A_NEGATIVO = 'A-';
    case B_POSITIVO = 'B+';
    case B_NEGATIVO = 'B-';
    case AB_POSITIVO = 'AB+';
    case AB_NEGATIVO = 'AB-';

    public function getLabel(): string
    {
        return $this->value;
    }
}

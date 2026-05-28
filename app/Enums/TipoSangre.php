<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoSangre: string
{
    case O_POSITIVO = 'O+';
    case O_NEGATIVO = 'O-';
    case A_POSITIVO = 'A+';
    case A_NEGATIVO = 'A-';
    case B_POSITIVO = 'B+';
    case B_NEGATIVO = 'B-';
    case AB_POSITIVO = 'AB+';
    case AB_NEGATIVO = 'AB-';

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->value])
            ->all();
    }
}

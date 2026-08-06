<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Gestion;

use Illuminate\Support\Str;

final class GenerarCodigoReserva
{
    public function ejecutar(): string
    {
        return 'RES-'.now()->format('Y').'-'.Str::upper(Str::random(10));
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Reservas;

use App\Repository\Models\Reservas\Reserva;

final class GenerarCodigoReserva
{
    public function ejecutar(): string
    {
        $year = date('Y');
        $maxId = Reserva::max('id');
        $ultimoId = is_numeric($maxId) ? (int) $maxId : 0;
        $siguienteNum = str_pad((string) ($ultimoId + 1), 4, '0', STR_PAD_LEFT);

        return "RES-{$year}-{$siguienteNum}";
    }
}

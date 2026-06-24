<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Models\Habitaciones\Habitacion;

class GenerarCodigoHabitacion
{
    public function execute(): string
    {
        $ultimo = Habitacion::withTrashed()
            ->where('codigo', 'like', 'HAB-%')
            ->latest('id')
            ->first();

        $numero = 1;
        if ($ultimo && preg_match('/^HAB-(\d+)$/', $ultimo->codigo, $matches)) {
            $numero = intval($matches[1]) + 1;
        } else {
            $maxId = Habitacion::withTrashed()->max('id');
            $numero = (is_numeric($maxId) ? (int) $maxId : 0) + 1;
        }

        return 'HAB-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }
}

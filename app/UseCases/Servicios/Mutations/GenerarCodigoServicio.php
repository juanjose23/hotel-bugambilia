<?php

declare(strict_types=1);

namespace App\UseCases\Servicios\Mutations;

use App\Models\Servicios\Servicio;

class GenerarCodigoServicio
{
    public function ejecutar(): string
    {
        $ultimo = Servicio::withTrashed()->latest('id')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;

        return 'SRV-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }
}

<?php

declare(strict_types=1);

namespace App\BusinessLogic\Colaboradores;

use App\Repository\Models\Colaboradores\Colaborador;

class GeneradorCodigoColaborador
{
    public function generar(): string
    {
        $ultimo = Colaborador::withTrashed()->latest('id')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;

        return 'COL-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }
}

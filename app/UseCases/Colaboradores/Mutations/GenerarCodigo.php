<?php

namespace App\UseCases\Colaboradores\Mutations;

use App\Models\Colaboradores\Colaborador;

class GenerarCodigo
{
    public function generarCodigo(): string
    {
        $ultimo = Colaborador::withTrashed()->latest('id')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 4)) + 1 : 1;

        return 'COL-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }
}

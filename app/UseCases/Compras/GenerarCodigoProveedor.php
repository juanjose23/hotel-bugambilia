<?php

namespace App\UseCases\Compras;

use App\Models\Compras\Proveedor;

class GenerarCodigoProveedor
{
    public function ejecutar(): string
    {
        $ultimo = Proveedor::withTrashed()->latest('id')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 5)) + 1 : 1;

        return 'PROV-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }
}

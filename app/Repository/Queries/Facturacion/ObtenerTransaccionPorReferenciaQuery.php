<?php

declare(strict_types=1);

namespace App\Repository\Queries\Facturacion;

use App\Repository\Models\Facturacion\PagoTransaccion;

final class ObtenerTransaccionPorReferenciaQuery
{
    public function ejecutar(string $referenciaPasarela): ?PagoTransaccion
    {
        return PagoTransaccion::query()
            ->where('referencia_pasarela', $referenciaPasarela)
            ->first();
    }
}

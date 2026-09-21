<?php

declare(strict_types=1);

namespace App\Actions\Ventas;

use App\Repository\Models\Cuentas\Venta;

final class GenerarNumeroVentaDirecta
{
    public function ejecutar(): string
    {
        $prefix = 'VNT-POS-'.now()->format('ymd').'-';
        $ultimo = Venta::query()
            ->where('numero_venta', 'like', $prefix.'%')
            ->count();

        return sprintf('%s%04d', $prefix, $ultimo + 1);
    }
}

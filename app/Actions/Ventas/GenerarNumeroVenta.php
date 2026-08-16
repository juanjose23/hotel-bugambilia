<?php

declare(strict_types=1);

namespace App\Actions\Ventas;

final class GenerarNumeroVenta
{
    public function ejecutar(int $cuentaId): string
    {
        return sprintf('VTA-%s-%06d', now()->format('Ymd'), $cuentaId);
    }
}

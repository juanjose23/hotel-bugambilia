<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Repository\Models\Facturacion\PagoTransaccion;

final readonly class PagoTransaccionPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): PagoTransaccion
    {
        /** @var PagoTransaccion $transaccion */
        $transaccion = PagoTransaccion::query()->create($datos);

        return $transaccion;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(PagoTransaccion $transaccion, array $datos): PagoTransaccion
    {
        $transaccion->update($datos);

        return $transaccion->refresh();
    }
}

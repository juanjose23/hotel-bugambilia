<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Repository\Models\Facturacion\PagoConciliacion;

final readonly class PagoConciliacionPersistencia
{
    /**
     * @param  array<string, mixed>  $atributos
     * @param  array<string, mixed>  $datos
     */
    public function actualizarOCrear(array $atributos, array $datos): PagoConciliacion
    {
        /** @var PagoConciliacion $conciliacion */
        $conciliacion = PagoConciliacion::query()->updateOrCreate($atributos, $datos);

        return $conciliacion;
    }

    public function buscarPorPagoTransaccionId(int $transaccionId): ?PagoConciliacion
    {
        /** @var PagoConciliacion|null $conciliacion */
        $conciliacion = PagoConciliacion::query()
            ->where('pago_transaccion_id', $transaccionId)
            ->first();

        return $conciliacion;
    }
}

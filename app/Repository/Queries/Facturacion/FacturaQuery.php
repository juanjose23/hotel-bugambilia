<?php

declare(strict_types=1);

namespace App\Repository\Queries\Facturacion;

use App\Enums\Facturacion\EstadoFactura;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaSerie;
use Illuminate\Database\Eloquent\Builder;

final readonly class FacturaQuery
{
    public function ventaParaFacturaConLock(Venta $venta): Venta
    {
        /** @var Venta $ventaRecargada */
        $ventaRecargada = Venta::query()
            ->with(['detalles', 'moneda', 'cuenta'])
            ->lockForUpdate()
            ->findOrFail($venta->id);

        return $ventaRecargada;
    }

    public function yaEmitidaParaVenta(int $ventaId): bool
    {
        return Factura::query()
            ->where('venta_id', $ventaId)
            ->where('estado', EstadoFactura::Emitida->value)
            ->exists();
    }

    /**
     * @param  list<string>  $with
     */
    public function porIdConLock(int $id, array $with = []): Factura
    {
        $query = Factura::query()->lockForUpdate();

        if ($with !== []) {
            $query->with($with);
        }

        /** @var Factura $factura */
        $factura = $query->findOrFail($id);

        return $factura;
    }

    public function serieActiva(?int $serieId): ?FacturaSerie
    {
        $query = FacturaSerie::query()
            ->where('activa', true)
            ->lockForUpdate();

        if ($serieId !== null) {
            $query->whereKey($serieId);
        }

        /** @var FacturaSerie|null $serie */
        $serie = $query->orderBy('id')->first();

        return $serie;
    }

    public function autorizacionActivaSerie(FacturaSerie $serie): ?FacturaAutorizacionDgi
    {
        /** @var FacturaAutorizacionDgi|null $autorizacion */
        $autorizacion = $serie->autorizaciones()
            ->where('activa', true)
            ->where(function (Builder $query): void {
                $query->whereNull('vence_at')
                    ->orWhere('vence_at', '>=', now()->toDateString());
            })
            ->orderByDesc('fecha_autorizacion')
            ->lockForUpdate()
            ->first();

        return $autorizacion;
    }
}

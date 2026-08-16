<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Facturacion;

use App\Enums\Facturacion\EstadoFolioFactura;
use App\Repository\Models\Facturacion\Factura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;

final readonly class FacturaFolioPersistencia
{
    /**
     * Reserva un folio y avanza el correlativo de la serie bajo el lock del caller.
     */
    public function reservar(FacturaSerie $serie, FacturaAutorizacionDgi $autorizacion, int $correlativo, string $numero, ?int $usuarioId): FacturaFolio
    {
        /** @var FacturaFolio $folio */
        $folio = FacturaFolio::query()->create([
            'factura_serie_id' => $serie->id,
            'factura_autorizacion_dgi_id' => $autorizacion->id,
            'numero_correlativo' => $correlativo,
            'numero' => $numero,
            'estado' => EstadoFolioFactura::Reservado,
            'reservado_at' => now(),
            'reservado_por' => $usuarioId,
        ]);

        $serie->update(['siguiente_numero' => $correlativo + 1]);

        return $folio;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(FacturaFolio $folio, array $datos): FacturaFolio
    {
        $folio->update($datos);

        return $folio->refresh();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarPorFactura(Factura $factura, array $datos): void
    {
        $factura->folios()->update($datos);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\Solicitud;

final class BuscarTrazabilidadPorCodigoQuery
{
    public function ejecutar(string $codigo): ?Solicitud
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        // 1. Buscar en Solicitud directamente
        $solicitud = Solicitud::where('codigo', $codigo)->first();

        // 2. Si no es, buscar por folio visual de cotización: COT-#123 o COT-123.
        if (! $solicitud) {
            $cotizacionId = $this->extraerCotizacionId($codigo);
            $solicitud = $cotizacionId !== null
                ? Solicitud::query()
                    ->whereHas('cotizaciones', fn ($query) => $query->whereKey($cotizacionId))
                    ->first()
                : null;
        }

        // 3. Si no es, buscar en Orden de Compra
        if (! $solicitud) {
            $oc = OrdenCompra::where('codigo', $codigo)->first();
            if ($oc) {
                $solicitud = $oc->solicitud;
            }
        }

        return $solicitud;
    }

    private function extraerCotizacionId(string $codigo): ?int
    {
        if (preg_match('/^COT-#?(\d+)$/i', $codigo, $matches) !== 1) {
            return null;
        }

        $id = (int) $matches[1];

        return $id > 0 ? $id : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Reportes;

use App\Repository\Models\Compras\Cotizacion;
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

        // 2. Si no es, buscar en Cotización
        if (! $solicitud) {
            $cotizacion = Cotizacion::where('codigo', $codigo)->first();
            if ($cotizacion) {
                $solicitud = $cotizacion->solicitud;
            }
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
}

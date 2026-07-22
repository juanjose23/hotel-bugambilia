<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\Solicitud;
use App\Support\HotelInfo;

final class GeneradorReportesCompra
{
    /**
     * @return array<string, mixed>
     */
    public function getReportData(mixed $record): array
    {
        if (is_object($record) && method_exists($record, 'load')) {
            if ($record instanceof Solicitud) {
                $record->load(['colaborador.persona', 'departamentoSolicitante', 'items.producto', 'items.variante', 'items.unidadMedida']);
            } elseif ($record instanceof OrdenCompra) {
                $record->load(['proveedor.persona', 'condicionPago', 'items.producto', 'items.variante', 'items.unidadMedida', 'cotizacion.moneda']);
            } elseif ($record instanceof RecepcionCompra) {
                $record->load(['ordenCompra.proveedor.persona', 'ordenCompra.cotizacion.moneda', 'receptor', 'items.producto', 'items.variante', 'items.unidadMedida']);
            } elseif ($record instanceof Cotizacion) {
                $record->load(['proveedor.persona', 'solicitud.items.unidadMedida', 'items.producto', 'items.variante']);
            } else {
                $record->load(['items.producto', 'items.variante']);
            }
        }

        return array_merge(HotelInfo::getBaseData(), ['record' => $record]);
    }
}

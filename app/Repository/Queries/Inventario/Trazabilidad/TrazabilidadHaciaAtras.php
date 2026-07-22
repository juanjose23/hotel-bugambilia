<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Trazabilidad;

use App\BusinessLogic\Inventario\Data\Trazabilidad\MovimientoTrazabilidadData;
use App\Repository\Models\Inventario\MovimientoStock;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * HTB-INV-012 — Trazabilidad Hacia Atrás (por Pedido / Consumo)
 * Dado un documento de salida, muestra de qué lotes se tomó el stock con sus recepciones originales.
 */
class TrazabilidadHaciaAtras
{
    /**
     * @param  string  $documentoTipo  Tipo del documento (ej. 'consumo', 'requisicion', 'devolucion_item')
     * @param  int  $documentoId  ID del documento de salida
     * @return Collection<int, MovimientoTrazabilidadData>
     */
    public function ejecutar(string $documentoTipo, int $documentoId): Collection
    {
        if (blank($documentoTipo)) {
            throw new RuntimeException('El tipo de documento es obligatorio para la trazabilidad hacia atrás.');
        }

        return MovimientoStock::query()
            ->with([
                'lote.recepcionItem.recepcion.ordenCompra.proveedor',
                'lote.producto:id,nombre',
                'lote.variante:id,producto_id,codigo,nombre_variante',
            ])
            ->where('documento_tipo', $documentoTipo)
            ->where('documento_id', $documentoId)
            ->orderBy('created_at')
            ->get()
            ->map(fn (MovimientoStock $m) => new MovimientoTrazabilidadData(
                id: (int) $m->id,
                tipo: $m->tipo,
                producto: $m->lote->producto->nombre ?? '',
                cantidad: (float) $m->cantidad,
                ubicacionOrigen: $m->ubicacionOrigen?->nombre,
                ubicacionDestino: $m->ubicacionDestino?->nombre,
                fecha: $m->created_at,
            ));
    }
}

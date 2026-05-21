<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Trazabilidad;

use App\Models\Inventario\MovimientoStock;
use Illuminate\Database\Eloquent\Collection;
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
     * @return Collection<int, MovimientoStock>
     */
    public function ejecutar(string $documentoTipo, int $documentoId): Collection
    {
        if (blank($documentoTipo)) {
            throw new RuntimeException('El tipo de documento es obligatorio para la trazabilidad hacia atrás.');
        }

        return MovimientoStock::with([
            'lote.recepcionItem.recepcion.ordenCompra.proveedor',
            'lote.producto',
            'lote.variante',
        ])
            ->where('documento_tipo', $documentoTipo)
            ->where('documento_id', $documentoId)
            ->orderBy('created_at')
            ->get();
    }
}

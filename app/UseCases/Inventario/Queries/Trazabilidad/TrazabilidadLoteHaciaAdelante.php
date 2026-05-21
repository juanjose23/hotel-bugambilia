<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Queries\Trazabilidad;

use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use Illuminate\Database\Eloquent\Collection;

/**
 * HTB-INV-011 — Trazabilidad Hacia Adelante (por Lote)
 * Dado un lote, muestra todos los movimientos y documentos desde la recepción hasta el consumo o baja.
 */
class TrazabilidadLoteHaciaAdelante
{
    /**
     * @return array{lote: Lote, movimientos: Collection<int, MovimientoStock>}
     */
    public function ejecutar(int $loteId): array
    {
        $lote = Lote::with([
            'producto',
            'variante',
            'ubicacion',
            'recepcionItem.recepcion.ordenCompra.proveedor',
        ])->findOrFail($loteId);

        $movimientos = MovimientoStock::with(['ubicacionOrigen', 'ubicacionDestino'])
            ->where('lote_id', $loteId)
            ->orderBy('created_at')
            ->get();

        return [
            'lote' => $lote,
            'movimientos' => $movimientos,
        ];
    }
}

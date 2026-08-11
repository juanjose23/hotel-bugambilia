<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Trazabilidad;

use App\BusinessLogic\Inventario\Data\Lotes\LoteAlertaData;
use App\BusinessLogic\Inventario\Data\Trazabilidad\MovimientoTrazabilidadData;
use App\BusinessLogic\Inventario\Data\Trazabilidad\TrazabilidadAdelanteData;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\MovimientoStock;
use Illuminate\Support\Collection;

/**
 * HTB-INV-011 — Trazabilidad Hacia Adelante (por Lote)
 * Dado un lote, muestra todos los movimientos y documentos desde la recepción hasta el consumo o baja.
 */
class TrazabilidadLoteHaciaAdelante
{
    public function ejecutar(int $loteId): TrazabilidadAdelanteData
    {
        $lote = $this->obtenerLote($loteId);

        return new TrazabilidadAdelanteData(
            lote: $this->mapearLote($lote),
            movimientos: $this->obtenerMovimientos($loteId),
        );
    }

    private function obtenerLote(int $loteId): Lote
    {
        return Lote::query()
            ->with([
                'producto:id,nombre',
                'variante:id,producto_id,codigo,nombre_variante',
                'ubicacion:id,nombre',
                'recepcionItem.recepcion.ordenCompra.proveedor',
            ])
            ->findOrFail($loteId);
    }

    /**
     * @return Collection<int, MovimientoTrazabilidadData>
     */
    private function obtenerMovimientos(int $loteId): Collection
    {
        return MovimientoStock::query()
            ->with([
                'ubicacionOrigen:id,nombre',
                'ubicacionDestino:id,nombre',
            ])
            ->where('lote_id', $loteId)
            ->orderBy('created_at')
            ->get()
            ->map(fn (MovimientoStock $m) => new MovimientoTrazabilidadData(
                id: (int) $m->id,
                tipo: $m->tipo,
                producto: $m->producto->nombre ?? '',
                cantidad: (float) $m->cantidad,
                ubicacionOrigen: $m->ubicacionOrigen?->nombre,
                ubicacionDestino: $m->ubicacionDestino?->nombre,
                fecha: $m->created_at,
            ));
    }

    private function mapearLote(Lote $lote): LoteAlertaData
    {
        return new LoteAlertaData(
            id: (int) $lote->id,
            codigoLote: $lote->codigo_lote,
            producto: $lote->producto->nombre ?? '',
            variante: $lote->variante->nombre_variante ?? $lote->variante->codigo ?? '',
            ubicacion: $lote->ubicacion->nombre ?? '',
            cantidadDisponible: (float) $lote->cantidad_disponible,
            fechaVencimiento: $lote->fecha_vencimiento,
            estado: $lote->estado,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Servicios\ServicioTraslados;
use App\Events\Inventario\LoteTrasladado;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class TrasladarLote
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioTraslados $servicioTraslados,
    ) {}

    public function ejecutar(
        int $loteId,
        int $destinoId,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
        ?int $destinoDetalleId = null,
    ): void {
        $lote = $this->modeloLote->findOrFail($loteId);
        $ubicacionOrigenId = (int) $lote->ubicacion_id;

        DB::transaction(function () use ($lote, $destinoId, $creadoPorId, $referencia, $notas, $destinoDetalleId) {
            $this->servicioTraslados->ejecutarTrasladoLote(
                lote: $lote,
                destinoId: $destinoId,
                creadoPorId: $creadoPorId,
                referencia: $referencia,
                notas: $notas,
                destinoDetalleId: $destinoDetalleId
            );
        });

        event(new LoteTrasladado(
            lote: $lote,
            ubicacionOrigenId: $ubicacionOrigenId,
            ubicacionDestinoId: $destinoId,
            trasladadoPorId: $creadoPorId ?? 0,
        ));
    }
}

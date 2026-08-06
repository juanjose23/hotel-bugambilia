<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Servicios\ServicioMermas;
use App\Events\Inventario\MermaRegistrada;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class RegistrarMerma
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioMermas $servicioMermas,
    ) {}

    public function ejecutar(
        int $loteId,
        float $cantidad,
        ?string $motivo = null,
        ?int $creadoPorId = null,
    ): void {
        $lote = $this->modeloLote->findOrFail($loteId);

        DB::transaction(function () use ($lote, $cantidad, $motivo, $creadoPorId) {
            $this->servicioMermas->ejecutarMerma($lote, $cantidad, $motivo, $creadoPorId);
        });

        event(new MermaRegistrada(
            lote: $lote,
            cantidad: $cantidad,
            motivo: $motivo ?? 'Sin motivo especificado',
            registradoPorId: $creadoPorId ?? 0,
        ));
    }
}

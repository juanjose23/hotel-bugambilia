<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes\EnviarACuarentena;

use App\BusinessLogic\Inventario\Servicios\ServicioCuarentena;
use App\Events\Inventario\LoteEnviadoACuarentena;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class EnviarACuarentena
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioCuarentena $servicioCuarentena,
    ) {}

    public function ejecutar(
        int $loteId,
        ?int $ubicacionCuarentenaId = null,
        ?string $motivo = null,
        ?int $creadoPorId = null,
    ): void {
        $lote = $this->modeloLote->findOrFail($loteId);

        DB::transaction(function () use ($lote, $ubicacionCuarentenaId, $motivo, $creadoPorId) {
            $this->servicioCuarentena->enviarACuarentena($lote, $ubicacionCuarentenaId, $motivo, $creadoPorId);
        });

        event(new LoteEnviadoACuarentena(
            lote: $lote,
            motivo: $motivo ?? 'Sin motivo especificado',
            creadoPorId: $creadoPorId ?? 0,
        ));
    }
}

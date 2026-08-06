<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Servicios\ServicioCuarentena;
use App\Events\Inventario\LoteRechazadoCuarentena;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class RechazarLotesCuarentena
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioCuarentena $servicioCuarentena,
    ) {}

    /**
     * @param  int[]  $loteIds
     * @return array<int, array{lote_id: int, codigo_lote: string}>
     */
    public function execute(array $loteIds, string $motivo, ?int $usuarioId = null): array
    {
        $resultado = [];

        $lotes = $this->modeloLote->query()->whereIn('id', $loteIds)->get();

        DB::transaction(function () use ($lotes, $motivo, $usuarioId, &$resultado) {

            foreach ($lotes as $lote) {
                $this->servicioCuarentena->rechazarLote($lote, $motivo, $usuarioId);

                event(new LoteRechazadoCuarentena(
                    lote: $lote,
                    motivo: $motivo,
                    rechazadoPorId: $usuarioId ?? 0,
                ));

                $resultado[] = [
                    'lote_id' => $lote->id,
                    'codigo_lote' => $lote->codigo_lote,
                ];
            }
        });

        return $resultado;
    }
}

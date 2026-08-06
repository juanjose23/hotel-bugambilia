<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Servicios\ServicioCuarentena;
use App\Events\Inventario\LoteLiberadoCuarentena;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class LiberarLotesCuarentena
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioCuarentena $servicioCuarentena,
    ) {}

    /**
     * @param  int[]  $loteIds
     * @return array<int, array{lote_id: int, codigo_lote: string}>
     */
    public function execute(array $loteIds, ?int $usuarioId = null): array
    {
        $resultado = [];

        $lotes = $this->modeloLote->query()->whereIn('id', $loteIds)->get();

        DB::transaction(function () use ($lotes, $usuarioId, &$resultado) {

            foreach ($lotes as $lote) {
                $this->servicioCuarentena->liberarLote($lote, $usuarioId);

                event(new LoteLiberadoCuarentena(
                    lote: $lote,
                    liberadoPorId: $usuarioId ?? 0,
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

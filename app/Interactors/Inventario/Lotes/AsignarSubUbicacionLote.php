<?php

declare(strict_types=1);

namespace App\Interactors\Inventario\Lotes;

use App\BusinessLogic\Inventario\Servicios\ServicioSubUbicacion;
use App\Repository\Models\Inventario\Lote;
use Illuminate\Support\Facades\DB;

class AsignarSubUbicacionLote
{
    public function __construct(
        private readonly Lote $modeloLote,
        private readonly ServicioSubUbicacion $servicioSubUbicacion,
    ) {}

    public function ejecutar(int $loteId, int $ubicacionDetalleId): void
    {
        $lote = $this->modeloLote->findOrFail($loteId);

        DB::transaction(function () use ($lote, $ubicacionDetalleId) {
            $this->servicioSubUbicacion->ejecutarAsignacion($lote, $ubicacionDetalleId);
        });
    }
}

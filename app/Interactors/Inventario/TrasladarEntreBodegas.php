<?php

declare(strict_types=1);

namespace App\Interactors\Inventario;

use App\BusinessLogic\Inventario\Servicios\ServicioTraslados;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use Illuminate\Support\Facades\DB;

class TrasladarEntreBodegas
{
    public function __construct(
        private readonly LoteRepositorioInterface $loteRepositorio,
        private readonly ServicioTraslados $servicioTraslados,
    ) {}

    public function execute(
        int $productoId,
        int $loteId,
        float $cantidad,
        int $origenId,
        int $destinoId,
        ?int $productoVarianteId = null,
        ?int $creadoPorId = null,
        ?string $referencia = null,
        ?string $notas = null,
        ?int $ubicacionDestinoDetalleId = null,
    ): void {

        DB::transaction(function () use (
            $productoId, $loteId, $cantidad, $origenId, $destinoId,
            $productoVarianteId, $creadoPorId, $referencia, $notas,
            $ubicacionDestinoDetalleId,
        ) {
            $lote = $this->loteRepositorio->buscarPorId($loteId);

            $this->servicioTraslados->ejecutarTrasladoEntreBodegas(
                productoId: $productoId,
                loteId: $loteId,
                cantidad: $cantidad,
                origenId: $origenId,
                destinoId: $destinoId,
                productoVarianteId: $productoVarianteId,
                creadoPorId: $creadoPorId,
                referencia: $referencia,
                notas: $notas,
                ubicacionDestinoDetalleId: $ubicacionDestinoDetalleId,
                lote: $lote
            );
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\EnviarALavanderiaData;
use App\BusinessLogic\Limpieza\Data\EnviarLavanderiaItemData;
use App\Interactors\Limpieza\Lavanderia\EnviarALavanderia;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Limpieza\LavanderiaProceso;
use App\Repository\Models\Shared\Stock as SharedStock;

final class ProcesadorEnvioBlancos
{
    public function __construct(
        private readonly EnviarALavanderia $enviarALavanderia,
    ) {}

    /** @param array<int|string, int|float|string> $blancosEnviar */
    public function procesar(array $blancosEnviar, string $tipoDestino, ?int $usuarioId, int $ejecucionId): void
    {
        $enviarItems = [];
        foreach ($blancosEnviar as $stockId => $qty) {
            $qty = (float) $qty;
            if ($qty <= 0) {
                continue;
            }

            $sharedStock = SharedStock::where('id', $stockId)->lockForUpdate()->firstOrFail();
            $enviarItems[] = EnviarLavanderiaItemData::fromArray([
                'stock_id' => $sharedStock->id,
                'tipo' => $tipoDestino,
                'cantidad' => $qty,
            ]);
        }

        if (empty($enviarItems)) {
            return;
        }

        $lavanderia = Ubicacion::where('tipo', 'lavanderia')->first();
        $lavanderiaId = $lavanderia?->id ?: throw new \RuntimeException("No existe una ubicación de tipo 'lavanderia' configurada.");

        $this->enviarALavanderia->execute(new EnviarALavanderiaData(
            items: $enviarItems,
            ubicacionLavanderiaId: $lavanderiaId,
            creadoPorId: $usuarioId,
            notas: "Envío a lavandería desde ejecución #{$ejecucionId}"
        ));

        foreach ($enviarItems as $item) {
            $ss = SharedStock::find($item->stockId);
            if ($ss) {
                $productoId = $ss->variante?->producto_id ?: 0;
                LavanderiaProceso::create([
                    'producto_id' => $productoId,
                    'producto_variante_id' => $ss->producto_variante_id,
                    'lote_id' => $ss->lote_id,
                    'cantidad' => $item->cantidad,
                    'estado' => 'en_proceso',
                ]);
            }
        }
    }
}

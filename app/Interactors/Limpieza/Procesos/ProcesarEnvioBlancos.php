<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\Data\EnviarALavanderiaData;
use App\BusinessLogic\Limpieza\Data\EnviarLavanderiaItemData;
use App\Interactors\Limpieza\Lavanderia\EnviarALavanderia;
use App\Repository\Persistencia\Catalogos\UbicacionRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use RuntimeException;

final readonly class ProcesarEnvioBlancos
{
    public function __construct(
        private EnviarALavanderia $enviarALavanderia,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
        private UbicacionRepositorioInterface $ubicacionRepositorio,
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

            $sharedStock = $this->limpiezaRepositorio->descontarSharedStockConLock((int) $stockId, 0);
            if (! $sharedStock) {
                throw new RuntimeException("Stock shared #{$stockId} no encontrado.");
            }

            $enviarItems[] = EnviarLavanderiaItemData::fromArray([
                'stock_id' => $sharedStock->id,
                'tipo' => $tipoDestino,
                'cantidad' => $qty,
            ]);
        }

        if (empty($enviarItems)) {
            return;
        }

        $lavanderia = $this->ubicacionRepositorio->buscarActivaPorTipo('lavanderia');
        $lavanderiaId = $lavanderia?->id ?: throw new RuntimeException("No existe una ubicación de tipo 'lavanderia' configurada.");

        $this->enviarALavanderia->execute(new EnviarALavanderiaData(
            items: $enviarItems,
            ubicacionLavanderiaId: (int) $lavanderiaId,
            creadoPorId: $usuarioId,
            notas: "Envío a lavandería desde ejecución #{$ejecucionId}"
        ));
    }
}

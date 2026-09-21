<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Stock;

use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\Interactors\Inventario\ConsumirStock;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Catalogos\ProductoRepositorioInterface;
use App\Repository\Persistencia\Limpieza\LavanderiaRepositorioInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final readonly class ReabastecerUbicacion
{
    public function __construct(
        private ConsumirStock $consumirStock,
        private LavanderiaRepositorioInterface $lavanderiaRepositorio,
        private ProductoRepositorioInterface $productoRepositorio,
    ) {}

    public function execute(ReabastecerUbicacionData $dto): void
    {
        $this->ejecutar($dto);
    }

    public function ejecutar(ReabastecerUbicacionData $dto): void
    {
        if (! in_array($dto->tipoDestino, ['habitacion', 'espacio', 'ubicacion'], true)) {
            throw new InvalidArgumentException("Tipo de destino inválido: {$dto->tipoDestino}");
        }

        $stockableType = match ($dto->tipoDestino) {
            'habitacion' => Habitacion::class,
            'espacio' => Espacio::class,
            'ubicacion' => Ubicacion::class,
        };

        $ubicacionDestinoId = $this->lavanderiaRepositorio->resolverUbicacionDestino($stockableType, $dto->destinoId);

        DB::transaction(function () use ($stockableType, $dto, $ubicacionDestinoId): void {
            foreach ($dto->items as $item) {
                $variant = $this->productoRepositorio->buscarVariantePorId((int) $item->productoVarianteId);
                $productoId = $variant ? (int) $variant->producto_id : 0;

                $detalle = $this->consumirStock->execute(
                    productoId: $productoId,
                    cantidadRequerida: $item->cantidad,
                    ubicacionId: $dto->bodegaOrigenId,
                    tipoMovimiento: 'TRASLADO',
                    productoVarianteId: $item->productoVarianteId,
                    creadoPorId: $dto->creadoPorId,
                    notas: $dto->notas,
                    referencia: "Reabastecimiento {$stockableType} #{$dto->destinoId}",
                    ubicacionDestinoId: $ubicacionDestinoId,
                );

                $cantidadConsumida = array_sum(array_column($detalle, 'cantidad'));
                $loteConsumidoId = collect($detalle)->firstWhere('lote_id', '!==', null)['lote_id'] ?? null;

                $this->lavanderiaRepositorio->reponerDestinoStock(
                    stockableType: $stockableType,
                    destinoId: $dto->destinoId,
                    varianteId: $item->productoVarianteId,
                    cantidad: (float) $cantidadConsumida,
                    loteId: $loteConsumidoId ? abs((int) $loteConsumidoId) : null,
                );
            }
        });
    }
}

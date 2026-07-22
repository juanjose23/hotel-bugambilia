<?php

declare(strict_types=1);

namespace App\BusinessLogic\Inventario;

use App\BusinessLogic\Inventario\Data\Pack\DisponibilidadPackData;
use App\BusinessLogic\Inventario\Data\Pack\ItemDisponibilidadData;
use App\Repository\Queries\Inventario\Pack\ObtenerStockItemsPackQuery;

class VerificarDisponibilidadPack
{
    public function __construct(
        private readonly ObtenerStockItemsPackQuery $obtenerStock,
    ) {}

    public function ejecutar(int $productoPadreId): DisponibilidadPackData
    {
        $items = $this->obtenerStock->ejecutar($productoPadreId);

        if ($items->isEmpty()) {
            return new DisponibilidadPackData(disponible: false, items: collect());
        }

        $itemsConEstado = $items->map(fn (object $item) => new ItemDisponibilidadData(
            varianteId: $item->varianteId,
            nombreVariante: $item->nombreVariante,
            codigo: $item->codigo,
            cantidadNecesaria: $item->cantidadNecesaria,
            stockTotal: $item->stockTotal,
            suficiente: $item->stockTotal >= $item->cantidadNecesaria,
        ));

        $disponible = $itemsConEstado->every(fn (ItemDisponibilidadData $item) => $item->suficiente === true);

        return new DisponibilidadPackData(disponible: $disponible, items: $itemsConEstado);
    }
}

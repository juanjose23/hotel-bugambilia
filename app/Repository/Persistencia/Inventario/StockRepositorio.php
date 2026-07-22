<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\Stock;

class StockRepositorio implements StockRepositorioInterface
{
    public function buscarPorLoteUbicacion(int $loteId, int $ubicacionId): ?Stock
    {
        return Stock::query()
            ->where('lote_id', $loteId)
            ->where('ubicacion_id', $ubicacionId)
            ->first();
    }

    public function buscarPorProductoUbicacion(int $productoId, ?int $varianteId, int $ubicacionId, bool $bloquear = false): ?Stock
    {
        $query = Stock::query()
            ->where('producto_id', $productoId)
            ->where('producto_variante_id', $varianteId)
            ->where('ubicacion_id', $ubicacionId);

        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Stock
    {
        return Stock::create($datos);
    }

    public function guardar(Stock $stock): void
    {
        $stock->save();
    }

    public function eliminar(Stock $stock): void
    {
        $stock->delete();
    }
}

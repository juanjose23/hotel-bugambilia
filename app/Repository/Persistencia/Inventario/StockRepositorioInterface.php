<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\Stock;

interface StockRepositorioInterface
{
    public function buscarPorLoteUbicacion(int $loteId, int $ubicacionId): ?Stock;

    public function buscarPorProductoUbicacion(int $productoId, ?int $varianteId, int $ubicacionId, bool $bloquear = false): ?Stock;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Stock;

    public function guardar(Stock $stock): void;

    public function eliminar(Stock $stock): void;
}

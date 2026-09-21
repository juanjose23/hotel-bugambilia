<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Inventario;

use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Inventario\Stock;
use App\Repository\Models\Shared\Stock as SharedStock;
use Illuminate\Support\Collection;

interface PackRepositorioInterface
{
    public function obtenerUbicacionDestino(string $stockableType, int $destinoId): ?int;

    /**
     * @return array{registrador: string, colaborador: string, colaborador_id: int|null}
     */
    public function resolverNombresYColaborador(?int $creadoPorId, ?int $colaboradorId): array;

    /**
     * @return Collection<int, ProductoKit>
     */
    public function obtenerItemsKit(int $productoPackId): Collection;

    /**
     * @param  array<int, int>  $varianteIds
     * @return Collection<(int|string), Collection<int, Stock>>
     */
    public function obtenerStocksDisponibles(int $bodegaOrigenId, array $varianteIds): Collection;

    /**
     * @param  array<int, int>  $varianteIds
     * @return Collection<(int|string), SharedStock>
     */
    public function obtenerStocksDestinoShared(string $stockableType, int $destinoId, array $varianteIds): Collection;

    public function guardarStockDestinoFisico(int $ubicacionId, int $productoId, int $varianteId, ?int $loteId, float $cantidad): void;

    public function guardarOActualizarStockDestinoShared(
        string $stockableType,
        int $destinoId,
        int $varianteId,
        ?int $loteId,
        float $cantidadIdeal,
        float $cantidadActual,
        mixed $stockDestinoExistente = null
    ): void;
}

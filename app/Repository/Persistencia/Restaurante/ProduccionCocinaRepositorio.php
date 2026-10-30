<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\ProcesoCocina;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Collection;

final class ProduccionCocinaRepositorio
{
    public function bloquear(int $id): ProcesoCocina
    {
        return ProcesoCocina::query()->with('productoOrigen')->lockForUpdate()->findOrFail($id);
    }

    public function variante(int $id): ProductoVariante
    {
        return ProductoVariante::query()->with(['producto.unidadMedida', 'unidadMedida'])->findOrFail($id);
    }

    /** @return Collection<int, ProductoKit> */
    public function ingredientes(int $productoId): Collection
    {
        return ProductoKit::query()->with(['variante.producto.unidadMedida', 'variante.unidadMedida'])->where('producto_padre_id', $productoId)->orderBy('producto_variante_id')->get();
    }

    /** @param array<string, mixed> $datos */
    public function crearLote(array $datos): Lote
    {
        return Lote::query()->create($datos);
    }

    /** @param array<string, mixed> $datos */
    public function confirmar(ProcesoCocina $proceso, array $datos): ProcesoCocina
    {
        $proceso->update($datos);

        return $proceso->refresh();
    }

    public function crearStock(Lote $lote, int $ubicacionId): void
    {
        Stock::query()->create(['stockable_type' => Ubicacion::class, 'stockable_id' => $ubicacionId, 'producto_id' => $lote->producto_id, 'producto_variante_id' => $lote->producto_variante_id, 'lote_id' => $lote->id, 'cantidad_ideal' => 0, 'cantidad_actual' => $lote->cantidad_inicial]);
    }
}

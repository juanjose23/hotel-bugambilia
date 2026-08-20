<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Carrito;

use App\Repository\Models\Inventario\MovimientoStock;
use Illuminate\Database\Eloquent\Collection;

final class ObtenerMovimientosRecientesCarrito
{
    /**
     * @return Collection<int, MovimientoStock>
     */
    public function execute(int $carritoId, int $limite = 15): Collection
    {
        return MovimientoStock::query()
            ->with(['producto', 'lote', 'ubicacionOrigen', 'ubicacionDestino', 'creadoPor.persona'])
            ->where(function ($query) use ($carritoId): void {
                $query->where('ubicacion_origen_id', $carritoId)
                    ->orWhere('ubicacion_destino_id', $carritoId);
            })
            ->latest()
            ->take($limite)
            ->get();
    }
}

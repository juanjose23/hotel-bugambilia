<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ejecucion;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Shared\Stock;
use Illuminate\Database\Eloquent\Collection;

class PrepararOperacionLimpieza
{
    /**
     * Carga la lista de blancos, consumos planificados y otros insumos requeridos para la limpieza.
     *
     * @return array{
     *     blancos: Collection<int, Stock>,
     *     consumos: Collection<int, Stock>,
     *     insumos: Collection<int, Stock>,
     *     adicionales: Collection<int, Stock>
     * }
     */
    public function execute(int $ejecucionId): array
    {
        $ejecucion = LimpiezaEjecucion::findOrFail($ejecucionId);
        $limpiableType = $ejecucion->limpiable_type;
        $limpiableId = $ejecucion->limpiable_id;

        $roomStocks = Stock::with(['variante.producto.categoria', 'lote'])
            ->where('stockable_type', $limpiableType)
            ->where('stockable_id', $limpiableId)
            ->get();

        $blancos = new Collection;
        $consumos = new Collection;
        $insumos = new Collection;
        $adicionales = new Collection;

        foreach ($roomStocks as $stock) {
            $categoria = $stock->variante?->producto?->categoria;
            $codigo = $categoria?->codigo ?: '';

            if (str_starts_with($codigo, 'CAT_PRO_BLAN_')) {
                $blancos->push($stock);
            } elseif (str_starts_with($codigo, 'CAT_PRO_AMEN_') || str_starts_with($codigo, 'CAT_PRO_ALIM_')) {
                $consumos->push($stock);
            } elseif (str_starts_with($codigo, 'CAT_PRO_LIMP_')) {
                $insumos->push($stock);
            } else {
                $adicionales->push($stock);
            }
        }

        return [
            'blancos' => $blancos,
            'consumos' => $consumos,
            'insumos' => $insumos,
            'adicionales' => $adicionales,
        ];
    }
}

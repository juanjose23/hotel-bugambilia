<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\BusinessLogic\Inventario\Estrategias\FEFOStrategy;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ObtenerDatosProcesoCocinaQuery
{
    public function __construct(
        private readonly ObtenerStockParaConsumo $stockQuery,
        private readonly FEFOStrategy $fefo,
    ) {}

    /**
     * @return array<int|string, string>
     */
    public function platosConReceta(): array
    {
        /** @var array<int|string, string> */
        return Plato::activos()
            ->whereNotNull('producto_receta_id')
            ->pluck('nombre', 'id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function productosDisponibles(): array
    {
        /** @var array<int|string, string> */
        return Producto::pluck('nombre', 'id')
            ->all();
    }

    /**
     * Devuelve el costo unitario real desde el primer lote disponible (FEFO).
     */
    public function costoRealUnitario(int $productoId): ?float
    {
        $cocinaRaw = Ubicacion::where('nombre', UbicacionCocina::RESTAURANTE->value)->value('id');
        $cocinaId = is_numeric($cocinaRaw) ? (int) $cocinaRaw : null;

        if ($cocinaId === null) {
            return null;
        }

        $stocks = $this->stockQuery->ejecutar($productoId, $cocinaId);

        if ($stocks->isEmpty()) {
            return null;
        }

        $lotesRaw = $stocks->pluck('lote')->filter(fn ($l) => $l instanceof Lote);

        if ($lotesRaw->isEmpty()) {
            return null;
        }

        /** @var array<int, Lote> $lotesArray */
        $lotesArray = $lotesRaw->values()->all();
        $loteCollection = new EloquentCollection($lotesArray);

        $seleccion = $this->fefo->seleccionarLotes($loteCollection, 1.0);

        if ($seleccion === []) {
            return null;
        }

        return $seleccion[0]['lote']->costo_unitario;
    }
}

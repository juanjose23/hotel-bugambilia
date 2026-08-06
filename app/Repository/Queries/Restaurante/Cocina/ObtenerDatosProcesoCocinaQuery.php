<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\BusinessLogic\Inventario\Estrategias\FEFOStrategy;
use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\Lote;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Inventario\Stock\ObtenerStockParaConsumo;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class ObtenerDatosProcesoCocinaQuery
{
    public function __construct(
        private readonly ObtenerStockParaConsumo $stockQuery,
        private readonly FEFOStrategy $fefo,
        private readonly RestauranteRepositorioInterface $repositorio,
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

    /**
     * @return array<int, array{
     *     producto_destino_id: int,
     *     variante_destino_id: int|null,
     *     cantidad: float,
     *     peso_unitario: null,
     *     peso_total: null,
     *     es_merma: bool,
     *     costo_asignado: float
     * }>
     */
    public function ingredientesParaPlato(int $platoId, int $cantidadPlatos): array
    {
        $plato = Plato::query()->with('receta')->find($platoId);

        if (! $plato instanceof Plato || $plato->producto_receta_id === null) {
            return [];
        }

        $ingredientes = $this->repositorio->obtenerIngredientesReceta((int) $plato->producto_receta_id);
        $cocina = $this->repositorio->obtenerUbicacionPorNombre(UbicacionCocina::RESTAURANTE->value);
        $cocinaId = $cocina?->id;
        $multiplicador = max($cantidadPlatos, 1);
        $rendimiento = (float) ($plato->receta->rendimiento_porciones ?? 1);
        $rendimiento = $rendimiento > 0 ? $rendimiento : 1.0;

        return $ingredientes
            ->map(function (ProductoKit $ingrediente) use ($cocinaId, $multiplicador, $rendimiento): array {
                $variante = $ingrediente->variante;
                $productoId = is_numeric($variante?->producto_id) ? (int) $variante->producto_id : 0;
                $varianteId = is_numeric($variante?->id) ? (int) $variante->id : null;
                $cantidad = round(((float) $ingrediente->cantidad / $rendimiento) * $multiplicador, 4);
                $costoUnitario = 0.0;

                if ($cocinaId !== null && $varianteId !== null) {
                    $stock = $this->repositorio->obtenerStockConLote((int) $cocinaId, $varianteId);
                    $costoUnitario = (float) ($stock?->lote->costo_unitario ?? 0.0);
                }

                if ($costoUnitario <= 0.0 && $productoId > 0) {
                    $costoUnitario = (float) ($this->costoRealUnitario($productoId) ?? 0.0);
                }

                return [
                    'producto_destino_id' => $productoId,
                    'variante_destino_id' => $varianteId,
                    'cantidad' => $cantidad,
                    'peso_unitario' => null,
                    'peso_total' => null,
                    'es_merma' => false,
                    'costo_asignado' => round($cantidad * $costoUnitario, 2),
                ];
            })
            ->filter(fn (array $item): bool => $item['producto_destino_id'] > 0)
            ->values()
            ->all();
    }
}

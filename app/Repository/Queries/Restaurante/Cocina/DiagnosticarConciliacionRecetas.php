<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Cocina;

use App\Enums\Restaurante\UbicacionCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;
use App\Repository\Models\Shared\Stock;

final class DiagnosticarConciliacionRecetas
{
    /**
     * @return array{
     *     resumen: array<string, int>,
     *     items: list<array<string, mixed>>
     * }
     */
    public function ejecutar(): array
    {
        $cocinaId = $this->ubicacionCocinaId();

        $platos = Plato::query()
            ->with(['receta', 'ingredientes.variante.producto'])
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $varianteIds = $platos
            ->flatMap(fn (Plato $plato) => $plato->ingredientes->pluck('producto_variante_id'))
            ->filter()
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->unique()
            ->values();

        $stocksMateria = $cocinaId !== null
            ? Stock::query()
                ->where('stockable_type', Ubicacion::class)
                ->where('stockable_id', $cocinaId)
                ->whereIn('producto_variante_id', $varianteIds)
                ->get()
                ->keyBy('producto_variante_id')
            : collect();

        $recetasTransformacion = RecetaTransformacionMateriaPrima::query()
            ->with(['varianteBruta.producto', 'varianteMateriaPrima.producto'])
            ->where('estado', true)
            ->whereIn('variante_materia_prima_id', $varianteIds)
            ->get()
            ->keyBy('variante_materia_prima_id');

        $varianteBrutaIds = $recetasTransformacion
            ->pluck('variante_bruta_id')
            ->filter()
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)
            ->unique()
            ->values();

        $stocksBruto = $cocinaId !== null
            ? Stock::query()
                ->where('stockable_type', Ubicacion::class)
                ->where('stockable_id', $cocinaId)
                ->whereIn('producto_variante_id', $varianteBrutaIds)
                ->get()
                ->keyBy('producto_variante_id')
            : collect();

        $items = [];
        $resumen = [
            'ok' => 0,
            'receta_incompleta' => 0,
            'variante_invalida' => 0,
            'falta_materia_prima' => 0,
            'puede_transformarse' => 0,
            'falta_bruto' => 0,
            'sin_regla_transformacion' => 0,
        ];

        foreach ($platos as $plato) {
            if ($plato->producto_receta_id === null || $plato->ingredientes->isEmpty()) {
                $items[] = [
                    'estado' => 'receta_incompleta',
                    'plato' => $plato->nombre,
                    'ingrediente' => 'Sin ingredientes configurados',
                    'detalle' => 'El plato no tiene producto receta o la receta no tiene ingredientes.',
                ];
                $resumen['receta_incompleta']++;

                continue;
            }

            foreach ($plato->ingredientes as $ingrediente) {
                if ($ingrediente->variante === null) {
                    $items[] = [
                        'estado' => 'variante_invalida',
                        'plato' => $plato->nombre,
                        'ingrediente' => 'Variante no encontrada',
                        'detalle' => 'La receta apunta a una variante inexistente o eliminada.',
                    ];
                    $resumen['variante_invalida']++;

                    continue;
                }

                $varianteId = (int) $ingrediente->producto_variante_id;
                $requerido = (float) $ingrediente->cantidad;
                $stock = $stocksMateria->get($varianteId);
                $disponible = $stock instanceof Stock ? (float) $stock->cantidad_actual : 0.0;
                $nombreIngrediente = $this->nombreVariante($ingrediente);

                if ($disponible >= $requerido) {
                    $items[] = [
                        'estado' => 'ok',
                        'plato' => $plato->nombre,
                        'ingrediente' => $nombreIngrediente,
                        'requerido' => $requerido,
                        'disponible' => $disponible,
                        'detalle' => 'Hay stock suficiente de materia prima.',
                    ];
                    $resumen['ok']++;

                    continue;
                }

                $recetaTransformacion = $recetasTransformacion->get($varianteId);
                if (! $recetaTransformacion instanceof RecetaTransformacionMateriaPrima) {
                    $items[] = [
                        'estado' => 'sin_regla_transformacion',
                        'plato' => $plato->nombre,
                        'ingrediente' => $nombreIngrediente,
                        'requerido' => $requerido,
                        'disponible' => $disponible,
                        'detalle' => 'Falta materia prima y no existe regla para saber desde qué material bruto producirla.',
                    ];
                    $resumen['sin_regla_transformacion']++;

                    continue;
                }

                $cantidadResultado = max((float) $recetaTransformacion->cantidad_resultado, 0.0001);
                $faltanteMateria = max(0.0, $requerido - $disponible);
                $brutoNecesario = ($faltanteMateria / $cantidadResultado) * (float) $recetaTransformacion->cantidad_bruta;
                $stockBruto = $stocksBruto->get($recetaTransformacion->variante_bruta_id);
                $brutoDisponible = $stockBruto instanceof Stock ? (float) $stockBruto->cantidad_actual : 0.0;

                if ($brutoDisponible >= $brutoNecesario) {
                    $items[] = [
                        'estado' => 'puede_transformarse',
                        'plato' => $plato->nombre,
                        'ingrediente' => $nombreIngrediente,
                        'requerido' => $requerido,
                        'disponible' => $disponible,
                        'bruto' => $this->nombreVarianteDesdeModelo($recetaTransformacion),
                        'bruto_necesario' => round($brutoNecesario, 4),
                        'bruto_disponible' => $brutoDisponible,
                        'detalle' => 'Falta materia prima, pero hay material bruto suficiente para producirla.',
                    ];
                    $resumen['puede_transformarse']++;

                    continue;
                }

                $items[] = [
                    'estado' => 'falta_bruto',
                    'plato' => $plato->nombre,
                    'ingrediente' => $nombreIngrediente,
                    'requerido' => $requerido,
                    'disponible' => $disponible,
                    'bruto' => $this->nombreVarianteDesdeModelo($recetaTransformacion),
                    'bruto_necesario' => round($brutoNecesario, 4),
                    'bruto_disponible' => $brutoDisponible,
                    'detalle' => 'Falta materia prima y también falta material bruto para producirla.',
                ];
                $resumen['falta_bruto']++;
            }
        }

        return [
            'resumen' => $resumen,
            'items' => $items,
        ];
    }

    private function ubicacionCocinaId(): ?int
    {
        $id = Ubicacion::query()
            ->where('nombre', UbicacionCocina::RESTAURANTE->value)
            ->orWhere('nombre', 'Cocina')
            ->orWhere('nombre', 'like', '%Cocina%')
            ->value('id');

        return is_numeric($id) ? (int) $id : null;
    }

    private function nombreVariante(ProductoKit $ingrediente): string
    {
        $producto = $ingrediente->variante?->producto?->nombre;
        $variante = $ingrediente->variante?->nombre_variante;

        return trim(($producto ? "{$producto} - " : '').($variante ?: "Variante {$ingrediente->producto_variante_id}"));
    }

    private function nombreVarianteDesdeModelo(RecetaTransformacionMateriaPrima $receta): string
    {
        $producto = $receta->varianteBruta?->producto?->nombre;
        $variante = $receta->varianteBruta?->nombre_variante;

        return trim(($producto ? "{$producto} - " : '').($variante ?: "Variante {$receta->variante_bruta_id}"));
    }
}

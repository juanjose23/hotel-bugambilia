<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Cocina;

use App\Interactors\Restaurante\Cocina\RegistrarReglaTransformacionMateriaPrima;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\RecetaTransformacionMateriaPrima;

/**
 * Recetas de transformación de materia prima en cocina (carnicería y salsería).
 * Reutiliza los ingredientes sembrados por el menú del restaurante.
 */
final class RecetaTransformacionMateriaPrimaSeeder extends BaseCocinaSeeder
{
    public function run(): void
    {
        $carne = $this->variantePorNombreProducto('Carne de res seleccionada');
        $carneMolida = $this->variantePorNombreProducto('Carne Molida Especial');
        $tomate = $this->variantePorNombreProducto('Tomate');
        $salsaPomodoro = $this->variantePorNombreProducto('Salsa Pomodoro Casera');

        $this->crearSiNoExiste(
            materia: $carne,
            bruta: $carneMolida,
            cantidadBruta: 10.0,
            cantidadResultado: 8.0,
            mermaEstimada: 2.0,
            observaciones: 'Carnicería: molienda de corte de res para ragú bolognese.',
        );

        $this->crearSiNoExiste(
            materia: $tomate,
            bruta: $salsaPomodoro,
            cantidadBruta: 10.0,
            cantidadResultado: 8.0,
            mermaEstimada: 2.0,
            observaciones: 'Salsería: concentración de tomate fresco para salsa pomodoro.',
        );

        $this->command->info('Cocina restaurante: recetas de transformación de materia prima sembradas.');
    }

    private function crearSiNoExiste(
        ?ProductoVariante $materia,
        ?ProductoVariante $bruta,
        float $cantidadBruta,
        float $cantidadResultado,
        float $mermaEstimada,
        string $observaciones,
    ): void {
        if (! $materia instanceof ProductoVariante || ! $bruta instanceof ProductoVariante) {
            $this->command->warn('Receta de transformación omitida: faltan los productos/variantes de materia prima o bruto.');

            return;
        }

        $existe = RecetaTransformacionMateriaPrima::query()
            ->where('variante_materia_prima_id', $materia->id)
            ->where('variante_bruta_id', $bruta->id)
            ->exists();

        if ($existe) {
            return;
        }

        app(RegistrarReglaTransformacionMateriaPrima::class)->ejecutar([
            'producto_materia_prima_id' => $materia->producto_id,
            'variante_materia_prima_id' => $materia->id,
            'producto_bruto_id' => $bruta->producto_id,
            'variante_bruta_id' => $bruta->id,
            'cantidad_bruta' => $cantidadBruta,
            'cantidad_resultado' => $cantidadResultado,
            'merma_estimada' => $mermaEstimada,
            'unidad_medida_id' => $materia->unidad_medida_id,
            'estado' => true,
            'observaciones' => $observaciones,
        ]);
    }
}

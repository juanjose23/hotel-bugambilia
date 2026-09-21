<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Cocina;

use App\Interactors\Restaurante\Cocina\TransformarMateriaPrimaCocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\TransformacionMateriaPrima;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;

/**
 * Transformaciones reales de materia prima en cocina, usando los interactors
 * del dominio y consumiendo el stock inicial de la cocina del restaurante.
 */
final class TransformacionMateriaPrimaSeeder extends BaseCocinaSeeder
{
    public function run(): void
    {
        try {
            $cocinaId = app(RestauranteRepositorioInterface::class)->obtenerUbicacionCocinaId();
        } catch (DomainException) {
            $this->command->warn('No hay ubicación de cocina configurada; se omite la siembra de transformaciones.');

            return;
        }

        $carne = $this->variantePorNombreProducto('Carne de res seleccionada');
        $carneMolida = $this->variantePorNombreProducto('Carne Molida Especial');
        $tomate = $this->variantePorNombreProducto('Tomate');
        $salsaPomodoro = $this->variantePorNombreProducto('Salsa Pomodoro Casera');

        $this->crearSiNoExiste(
            codigo: 'TMP-SEED-CARNE',
            origen: $carne,
            destino: $carneMolida,
            cantidadProcesada: 10.0,
            cantidadObtenida: 8.0,
            merma: 2.0,
            observaciones: 'Molienda de 10 kg de res para obtener carne molida especial.',
            cocinaId: $cocinaId,
        );

        $this->crearSiNoExiste(
            codigo: 'TMP-SEED-TOMATE',
            origen: $tomate,
            destino: $salsaPomodoro,
            cantidadProcesada: 10.0,
            cantidadObtenida: 8.0,
            merma: 2.0,
            observaciones: 'Concentración de 10 kg de tomate fresco para salsa pomodoro casera.',
            cocinaId: $cocinaId,
        );

        $this->command->info('Cocina restaurante: transformaciones de materia prima sembradas.');
    }

    private function crearSiNoExiste(
        string $codigo,
        ?ProductoVariante $origen,
        ?ProductoVariante $destino,
        float $cantidadProcesada,
        float $cantidadObtenida,
        float $merma,
        string $observaciones,
        int $cocinaId,
    ): void {
        if (! $origen instanceof ProductoVariante || ! $destino instanceof ProductoVariante) {
            $this->command->warn('Transformación de materia prima omitida: faltan los productos/variantes origen o destino.');

            return;
        }

        $existe = TransformacionMateriaPrima::query()
            ->where('codigo', $codigo)
            ->exists();

        if ($existe) {
            return;
        }

        app(TransformarMateriaPrimaCocina::class)->ejecutar([
            'codigo' => $codigo,
            'producto_origen_id' => $origen->producto_id,
            'variante_origen_id' => $origen->id,
            'ubicacion_origen_id' => $cocinaId,
            'cantidad_procesada' => $cantidadProcesada,
            'observaciones' => $observaciones,
            'items' => [
                [
                    'producto_destino_id' => $destino->producto_id,
                    'variante_destino_id' => $destino->id,
                    'cantidad' => $cantidadObtenida,
                    'costo_asignado' => 800.0,
                    'es_merma' => false,
                    'observaciones' => 'Materia prima obtenida de la transformación.',
                ],
                [
                    'producto_destino_id' => $destino->producto_id,
                    'variante_destino_id' => $destino->id,
                    'cantidad' => $merma,
                    'costo_asignado' => 200.0,
                    'es_merma' => true,
                    'observaciones' => 'Merma estimada de la transformación.',
                ],
            ],
        ], $this->usuarioId());
    }
}

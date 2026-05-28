<?php

declare(strict_types=1);

namespace App\UseCases\Habitaciones\Mutations;

use App\Models\Habitaciones\HabitacionStock;
use App\Models\Inventario\MovimientoStock;
use Illuminate\Support\Facades\DB;

class RegistrarConsumoHabitacion
{
    public function execute(
        int $habitacionStockId,
        float $cantidad,
        string $motivo = 'consumo',
        ?int $creadoPorId = null,
        ?string $referencia = null,
    ): void {
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad a consumir debe ser mayor a cero.');
        }

        DB::transaction(function () use ($habitacionStockId, $cantidad, $motivo, $creadoPorId, $referencia) {
            $stock = HabitacionStock::with(['habitacion', 'variante.producto'])->findOrFail($habitacionStockId);
            $variante = $stock->variante;
            $habitacion = $stock->habitacion;

            if (! $variante || ! $habitacion) {
                throw new \RuntimeException('Registro de stock incompleto: faltan relaciones.');
            }

            $producto = $variante->producto;
            if (! $producto) {
                throw new \RuntimeException("Variante ID {$variante->id} no tiene producto asociado.");
            }

            if ((float) $stock->cantidad_actual < $cantidad) {
                throw new \RuntimeException(sprintf(
                    'Stock insuficiente en la habitación. Actual: %s, Requerido: %s',
                    $stock->cantidad_actual,
                    $cantidad
                ));
            }

            $stock->cantidad_actual -= $cantidad;
            $stock->ultima_verificacion = now();
            $stock->save();

            MovimientoStock::create([
                'tipo' => 'CONSUMO',
                'lote_id' => $stock->lote_id,
                'producto_id' => $producto->id,
                'cantidad' => -$cantidad,
                'ubicacion_origen_id' => $habitacion->ubicacion_id,
                'ubicacion_destino_id' => null,
                'documento_tipo' => 'consumo_habitacion',
                'documento_id' => $stock->id,
                'referencia' => $referencia ?: sprintf(
                    'Consumo en habitación %s: %s',
                    $habitacion->codigo,
                    $motivo
                ),
                'creado_por_id' => $creadoPorId,
                'notas' => $motivo,
            ]);
        });
    }
}

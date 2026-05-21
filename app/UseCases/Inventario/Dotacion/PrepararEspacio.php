<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Dotacion;

use App\Models\Espacios\PlantillaDotacion;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use Illuminate\Support\Facades\DB;

class PrepararEspacio
{
    public function __construct(
        private readonly ConsumirStock $consumirStock
    ) {}

    /**
     * Aplica una plantilla de dotación a un espacio (habitación o área),
     * consumiendo los ítems correspondientes de una bodega (ubicación física) usando FEFO.
     */
    public function execute(
        string $espacioTipo,
        int $espacioId,
        int $plantillaId,
        int $ubicacionId,
        ?int $usuarioId = null,
        ?string $notas = null
    ): void {
        if (! in_array($espacioTipo, ['habitacion', 'area'], true)) {
            throw new \InvalidArgumentException('El tipo de espacio debe ser "habitacion" o "area".');
        }

        DB::transaction(function () use ($espacioTipo, $espacioId, $plantillaId, $ubicacionId, $usuarioId, $notas) {
            // Obtener la plantilla de dotación
            $plantilla = PlantillaDotacion::with(['items'])->findOrFail($plantillaId);

            if (! $plantilla->activa) {
                throw new \RuntimeException('La plantilla de dotación no está activa.');
            }

            foreach ($plantilla->items as $item) {
                $productoId = $item->producto_id;
                $productoVarianteId = $item->producto_variante_id;
                $cantidad = (float) $item->cantidad;

                if ($cantidad <= 0) {
                    continue;
                }

                // Consumir del stock en la bodega
                $this->consumirStock->execute(
                    productoId: $productoId,
                    cantidadRequerida: $cantidad,
                    ubicacionId: $ubicacionId,
                    tipoMovimiento: 'SALIDA_DOTACION',
                    productoVarianteId: $productoVarianteId,
                    documentoId: $espacioId,
                    documentoTipo: $espacioTipo,
                    creadoPorId: $usuarioId,
                    referencia: sprintf('Preparación de %s ID %d con plantilla %s', $espacioTipo, $espacioId, $plantilla->nombre),
                    notas: $notas
                );
            }
        });
    }
}

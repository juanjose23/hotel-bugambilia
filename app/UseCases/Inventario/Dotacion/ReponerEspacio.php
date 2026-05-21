<?php

declare(strict_types=1);

namespace App\UseCases\Inventario\Dotacion;

use App\Models\Espacios\PlantillaDotacion;
use App\UseCases\Inventario\Movimientos\Mutations\ConsumirStock;
use Illuminate\Support\Facades\DB;

class ReponerEspacio
{
    public function __construct(
        private readonly ConsumirStock $consumirStock
    ) {}

    /**
     * Realiza la reposición diaria de consumibles de un espacio (habitación o área),
     * consumiendo únicamente aquellos ítems marcados con es_reposicion_diaria = true
     * desde una bodega física usando FEFO.
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
            $plantilla = PlantillaDotacion::with(['items'])->findOrFail($plantillaId);

            if (! $plantilla->activa) {
                throw new \RuntimeException('La plantilla de dotación no está activa.');
            }

            foreach ($plantilla->items as $item) {
                if (! $item->es_reposicion_diaria) {
                    continue;
                }

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
                    tipoMovimiento: 'SALIDA_REPOSICION',
                    productoVarianteId: $productoVarianteId,
                    documentoId: $espacioId,
                    documentoTipo: $espacioTipo,
                    creadoPorId: $usuarioId,
                    referencia: sprintf('Reposición diaria de %s ID %d con plantilla %s', $espacioTipo, $espacioId, $plantilla->nombre),
                    notas: $notas
                );
            }
        });
    }
}

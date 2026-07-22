<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Procesos;

use App\BusinessLogic\Limpieza\ProcesadorConsumoAmenities;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

class ProcesarConsumosLimpieza
{
    public function __construct(
        private readonly ProcesadorConsumoAmenities $procesadorConsumoAmenities,
        private readonly ProcesadorReposicionConsumos $procesadorReposicionConsumos,
    ) {}

    /** @param array<string, mixed> $data */
    public function ejecutar(LimpiezaEjecucion $ejecucion, array $data, ?int $usuarioId, ?int $carritoId, string $tipoDestino): void
    {
        /** @var array<int, float|string> $consumosCantidad */
        $consumosCantidad = $data['consumos_cantidad'] ?? [];
        $this->procesadorConsumoAmenities->procesar($consumosCantidad, (int) $ejecucion->id, $usuarioId);

        /** @var array<int|string, int|float|string> $consumosReponer */
        $consumosReponer = $data['consumos_reponer'] ?? [];
        $this->procesadorReposicionConsumos->procesar(
            $consumosReponer,
            $carritoId,
            $tipoDestino,
            (int) $ejecucion->limpiable_id,
            $usuarioId,
            (int) $ejecucion->id,
        );
    }
}

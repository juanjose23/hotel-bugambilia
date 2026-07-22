<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Data\ReabastecerItemData;
use App\BusinessLogic\Limpieza\Data\ReabastecerUbicacionData;
use App\BusinessLogic\Limpieza\Data\TerminarLimpiezaData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Interactors\Limpieza\Stock\ReabastecerUbicacion;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final class FinalizadorEjecucionLimpieza
{
    public function __construct(
        private readonly ReabastecerUbicacion $reabastecerUbicacion,
    ) {}

    public function finalizar(LimpiezaEjecucion $ejecucion, TerminarLimpiezaData $dto): void
    {
        $estado = $this->tieneDiscrepancia($dto->checklist)
            ? EstadoLimpieza::CompletadaConDiscrepancia
            : EstadoLimpieza::Completada;

        $ejecucion->update([
            'estado' => $estado,
            'hora_fin' => now()->format('H:i:s'),
            'detalles_checklist' => $dto->checklist,
            'observaciones' => $dto->observaciones,
            'consumos' => $dto->consumos,
        ]);

        if ($ejecucion->carrito_id && ! empty($dto->consumos)) {
            $this->registrarConsumos($ejecucion, $dto->consumos);
        }
    }

    /** @param array<int|string, bool> $checklist */
    private function tieneDiscrepancia(array $checklist): bool
    {
        foreach ($checklist as $completed) {
            if (! $completed) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int|string, float> $consumos */
    private function registrarConsumos(LimpiezaEjecucion $ejecucion, array $consumos): void
    {
        $tipoDestino = match ($ejecucion->limpiable_type) {
            Habitacion::class => 'habitacion',
            Espacio::class => 'espacio',
            Ubicacion::class => 'ubicacion',
            default => null,
        };

        if ($tipoDestino === null) {
            return;
        }

        $items = [];
        foreach ($consumos as $varianteId => $cantidad) {
            if ($cantidad > 0) {
                $items[] = [
                    'producto_variante_id' => (int) $varianteId,
                    'cantidad' => (float) $cantidad,
                ];
            }
        }

        if (empty($items)) {
            return;
        }

        $this->reabastecerUbicacion->execute(
            new ReabastecerUbicacionData(
                tipoDestino: $tipoDestino,
                destinoId: $ejecucion->limpiable_id,
                items: array_map(
                    fn (array $item) => ReabastecerItemData::fromArray($item),
                    $items
                ),
                bodegaOrigenId: (int) $ejecucion->carrito_id,
                creadoPorId: auth()->id() !== null ? (int) auth()->id() : null,
                notas: "Consumo registrado al completar ejecución de limpieza #{$ejecucion->id}.",
            )
        );
    }
}

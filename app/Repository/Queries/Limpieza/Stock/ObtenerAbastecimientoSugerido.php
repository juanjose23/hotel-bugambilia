<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Stock;

use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Shared\Stock;

class ObtenerAbastecimientoSugerido
{
    /**
     * @return array<int, array{nombre: string, cantidad: float, detalles: array<int, string>}>
     */
    public function execute(int $colaboradorId): array
    {
        $executions = LimpiezaEjecucion::with('limpiable')
            ->where('colaborador_id', $colaboradorId)
            ->whereIn('estado', [EstadoLimpieza::Pendiente, EstadoLimpieza::EnProgreso])
            ->get();

        $habitacionIds = $executions
            ->filter(fn ($e) => $e->limpiable_type === Habitacion::class && $e->limpiable)
            ->pluck('limpiable.id')
            ->toArray();

        $allStocks = Stock::with(['variante.producto'])
            ->where('stockable_type', Habitacion::class)
            ->whereIn('stockable_id', $habitacionIds)
            ->get()
            ->groupBy('stockable_id');

        $sugerencias = [];

        foreach ($executions as $e) {
            if ($e->limpiable_type !== Habitacion::class) {
                continue;
            }

            /** @var Habitacion|null $habitacion */
            $habitacion = $e->limpiable;
            if (! $habitacion) {
                continue;
            }

            $roomStocks = $allStocks->get($habitacion->id, collect());

            foreach ($roomStocks as $rs) {
                $ideal = (float) $rs->cantidad_ideal;
                $actual = (float) $rs->cantidad_actual;

                if ($actual >= $ideal || ! $rs->variante) {
                    continue;
                }

                $faltante = $ideal - $actual;
                $varianteId = $rs->variante->id;
                $nombre = ($rs->variante->producto->nombre ?? '').($rs->variante->nombre_variante ? " ({$rs->variante->nombre_variante})" : '');

                if (! isset($sugerencias[$varianteId])) {
                    $sugerencias[$varianteId] = [
                        'nombre' => $nombre,
                        'cantidad' => 0.0,
                        'detalles' => [],
                    ];
                }

                $sugerencias[$varianteId]['cantidad'] += $faltante;
                $sugerencias[$varianteId]['detalles'][] = "Hab. {$habitacion->numero}: {$faltante}";
            }
        }

        return $sugerencias;
    }
}

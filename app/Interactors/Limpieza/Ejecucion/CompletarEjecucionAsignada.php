<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Data\TerminarLimpiezaData;
use App\BusinessLogic\Limpieza\Exceptions\OperacionLimpiezaNoPermitida;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Ejecucion\ObtenerEjecucionParaActualizar;
use Illuminate\Support\Facades\DB;

final class CompletarEjecucionAsignada
{
    public function __construct(
        private readonly ObtenerEjecucionParaActualizar $obtenerEjecucion,
        private readonly TerminarLimpieza $terminarLimpieza,
    ) {}

    /**
     * @param  array<int|string, bool>  $checklist
     * @param  array<int|string, float>  $consumos
     */
    public function execute(
        int $ejecucionId,
        int $colaboradorId,
        array $checklist,
        string $observaciones,
        array $consumos,
    ): LimpiezaEjecucion {
        return DB::transaction(function () use ($ejecucionId, $colaboradorId, $checklist, $observaciones, $consumos): LimpiezaEjecucion {
            $ejecucion = $this->obtenerEjecucion->execute($ejecucionId);

            if ($ejecucion->estado !== EstadoLimpieza::EnProgreso) {
                throw new OperacionLimpiezaNoPermitida('La tarea no está en progreso o ya fue completada.');
            }

            if ($ejecucion->colaborador_id !== $colaboradorId) {
                throw new OperacionLimpiezaNoPermitida('No puede completar una tarea asignada a otro colaborador.');
            }

            $this->terminarLimpieza->execute(new TerminarLimpiezaData(
                record: $ejecucion,
                checklist: $checklist,
                observaciones: $observaciones,
                consumos: $consumos,
            ));

            return $ejecucion->refresh();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\ActualizadorEstadoEspacioLimpieza;
use App\BusinessLogic\Limpieza\Data\TerminarLimpiezaData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use Illuminate\Support\Facades\DB;

class TerminarLimpieza
{
    public function __construct(
        private readonly FinalizadorEjecucionLimpieza $finalizadorEjecucion,
        private readonly ActualizadorEstadoEspacioLimpieza $actualizadorEstado,
    ) {}

    public function execute(TerminarLimpiezaData $dto): void
    {
        DB::transaction(function () use ($dto) {
            $ejecucion = $this->resolveEjecucion($dto->record);
            $solicitud = $this->resolveSolicitud($dto->record);

            if ($ejecucion) {
                $this->finalizadorEjecucion->finalizar($ejecucion, $dto);
            }

            if ($solicitud) {
                $solicitud->update(['estado' => EstadoLimpieza::Completada]);
            }

            $this->actualizadorEstado->actualizar($dto->record, $ejecucion);
        });
    }

    private function resolveEjecucion(LimpiezaEjecucion|SolicitudLimpieza $record): ?LimpiezaEjecucion
    {
        if ($record instanceof LimpiezaEjecucion) {
            return $record;
        }

        return LimpiezaEjecucion::where('solicitud_id', $record->id)->first();
    }

    private function resolveSolicitud(LimpiezaEjecucion|SolicitudLimpieza $record): ?SolicitudLimpieza
    {
        if ($record instanceof SolicitudLimpieza) {
            return $record;
        }

        /** @var ?SolicitudLimpieza */
        return $record->solicitud()->first();
    }
}

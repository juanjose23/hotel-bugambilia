<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\ActualizadorEstadoEspacioLimpieza;
use App\BusinessLogic\Limpieza\Data\TerminarLimpiezaData;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class TerminarLimpieza
{
    public function __construct(
        private FinalizarEjecucionLimpieza $finalizarEjecucion,
        private ActualizadorEstadoEspacioLimpieza $actualizadorEstado,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    public function execute(TerminarLimpiezaData $dto): void
    {
        $this->ejecutar($dto);
    }

    public function ejecutar(TerminarLimpiezaData $dto): void
    {
        DB::transaction(function () use ($dto): void {
            $ejecucion = $this->resolveEjecucion($dto->record);
            $solicitud = $this->resolveSolicitud($dto->record);

            if ($ejecucion) {
                $this->finalizarEjecucion->finalizar($ejecucion, $dto);
            }

            if ($solicitud) {
                $this->limpiezaRepositorio->actualizarSolicitud($solicitud, ['estado' => EstadoLimpieza::Completada]);
            }

            $this->actualizadorEstado->actualizar($dto->record, $ejecucion);
        });
    }

    private function resolveEjecucion(LimpiezaEjecucion|SolicitudLimpieza $record): ?LimpiezaEjecucion
    {
        if ($record instanceof LimpiezaEjecucion) {
            return $record;
        }

        return $this->limpiezaRepositorio->buscarEjecucionPorSolicitudId((int) $record->id);
    }

    private function resolveSolicitud(LimpiezaEjecucion|SolicitudLimpieza $record): ?SolicitudLimpieza
    {
        if ($record instanceof SolicitudLimpieza) {
            return $record;
        }

        /** @var ?SolicitudLimpieza $solicitud */
        $solicitud = $record->solicitud()->first();

        return $solicitud;
    }
}

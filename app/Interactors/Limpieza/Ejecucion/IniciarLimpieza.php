<?php

declare(strict_types=1);

namespace App\Interactors\Limpieza\Ejecucion;

use App\BusinessLogic\Limpieza\Data\IniciarLimpiezaData;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Limpieza\EstadoLimpieza;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Persistencia\Limpieza\LimpiezaRepositorioInterface;
use App\Repository\Queries\Limpieza\Carrito\BloquearCarritoParaLimpieza;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class IniciarLimpieza
{
    public function __construct(
        private BloquearCarritoParaLimpieza $bloquearCarrito,
        private LimpiezaRepositorioInterface $limpiezaRepositorio,
    ) {}

    public function execute(IniciarLimpiezaData $dto): void
    {
        $this->ejecutar($dto);
    }

    public function ejecutar(IniciarLimpiezaData $dto): void
    {
        DB::transaction(function () use ($dto): void {
            $record = $dto->record;
            $colaboradorOrPersonalId = $dto->colaboradorOrPersonalId;
            $carritoId = $dto->carritoId;
            $usuarioId = $dto->usuarioId;

            $ejecucion = null;
            $solicitud = null;

            if ($record instanceof LimpiezaEjecucion) {
                $ejecucion = $record;
                $record->loadMissing('solicitud');
                $solicitud = $record->solicitud;
            } elseif ($record instanceof SolicitudLimpieza) {
                $solicitud = $record;
                $ejecucion = $this->limpiezaRepositorio->buscarEjecucionPorSolicitudId((int) $record->id);
            }

            $record->loadMissing('limpiable');
            $limpiable = $record->getRelation('limpiable');
            $estadoPrevioVal = null;
            if ($limpiable instanceof Habitacion) {
                $estadoPrevioVal = $limpiable->estado->value;

                $this->limpiezaRepositorio->actualizarEstadoLimpiable($limpiable, EstadoEspacio::EN_LIMPIEZA);
            } elseif ($limpiable instanceof Espacio) {
                $this->limpiezaRepositorio->actualizarEstadoLimpiable($limpiable, EstadoEspacio::Limpieza);
            }

            if ($ejecucion) {
                $colaboradorId = null;
                if ($record instanceof LimpiezaEjecucion) {
                    $colaboradorId = $colaboradorOrPersonalId;
                } else {
                    $userId = $colaboradorOrPersonalId ?: $usuarioId;
                    $colaborador = $userId ? $this->limpiezaRepositorio->buscarColaboradorPorUserId((int) $userId) : null;
                    $colaboradorId = $colaborador?->id;
                }

                if ($carritoId) {
                    $this->bloquearCarrito->execute((int) $carritoId, (int) $ejecucion->id, is_numeric($colaboradorId) ? (int) $colaboradorId : null);
                }

                $this->limpiezaRepositorio->actualizarEjecucion($ejecucion, [
                    'estado' => EstadoLimpieza::EnProgreso,
                    'colaborador_id' => $colaboradorId,
                    'carrito_id' => $carritoId,
                    'hora_inicio' => Carbon::now()->format('H:i:s'),
                    'estado_previo' => $estadoPrevioVal,
                ]);
            }

            if ($solicitud) {
                $userId = null;
                if ($record instanceof SolicitudLimpieza) {
                    $userId = $colaboradorOrPersonalId ?: $usuarioId;
                } else {
                    $colabId = $colaboradorOrPersonalId;
                    if ($colabId) {
                        $colaborador = $this->limpiezaRepositorio->buscarColaboradorPorUserId((int) $colabId);
                        $userId = $colaborador?->persona?->user?->id;
                    }
                    $userId ??= $usuarioId;
                }

                $this->limpiezaRepositorio->actualizarSolicitud($solicitud, [
                    'estado' => EstadoLimpieza::EnProgreso,
                    'personal_id' => $userId,
                ]);
            }
        });
    }
}

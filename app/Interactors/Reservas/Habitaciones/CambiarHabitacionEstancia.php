<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\CambiarHabitacionData;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\Enums\Estancias\EstadoEstancia;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Events\Reservas\HabitacionPendienteDeLimpieza;
use App\Events\Reservas\HuespedCambiadoDeHabitacion;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class CambiarHabitacionEstancia
{
    public function __construct(
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private ReservaRepositorioInterface $reservas,
        private HabitacionRepositorioInterface $habitaciones,
    ) {}

    public function ejecutar(CambiarHabitacionData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $estancia = $this->reservas->estanciaConLock($data->estanciaId);

            if (! in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
                throw new DomainException("La estancia #{$estancia->id} no se encuentra activa para realizar un cambio de habitación.");
            }

            /** @var Habitacion $habitacionAnterior */
            $habitacionAnterior = $this->habitaciones->buscarPorIdConLock((int) $estancia->habitacion_id);

            $habitacionNueva = $this->habitaciones->buscarPorIdConLock($data->nuevaHabitacionId);

            if (! in_array($habitacionNueva->estado, [EstadoEspacio::Disponible, EstadoEspacio::Reservado], true)) {
                throw new DomainException("La nueva habitación {$habitacionNueva->nombre} (N° {$habitacionNueva->numero}) no está limpia y disponible para ocupar.");
            }

            $detalle = $this->reservas->obtenerDetalleDeEstanciaConLock($estancia);
            if ($detalle) {
                if ($detalle->fecha_fin === null) {
                    throw new DomainException("La estancia #{$estancia->id} no tiene una fecha de salida programada.");
                }

                // Validate availability of new room resource for remaining stay period
                $this->validarDisponibilidad->validarDisponibilidad(
                    fechaCheckIn: now(),
                    fechaCheckOut: $detalle->fecha_fin,
                    recursosReservablesIds: [$data->nuevoRecursoReservableId],
                    adultos: $detalle->adultos,
                    ninos: $detalle->ninos,
                    excluirDetalleId: $detalle->id,
                );

                $this->reservas->actualizarDetalle($detalle, [
                    'reservable_id' => $data->nuevoRecursoReservableId,
                ]);
            }

            // Move estancia to new room
            $this->reservas->actualizarEstancia($estancia, [
                'habitacion_id' => $habitacionNueva->id,
            ]);

            // Set old room to dirty, new room to occupied
            $this->habitaciones->actualizarEstado($habitacionAnterior, EstadoEspacio::Sucio);
            $this->habitaciones->actualizarEstado($habitacionNueva, EstadoEspacio::Ocupado);

            HabitacionPendienteDeLimpieza::dispatch($habitacionAnterior, "Cambio de habitación por motivo: {$data->motivo}");
            HuespedCambiadoDeHabitacion::dispatch($estancia, $habitacionAnterior, $habitacionNueva, $data->motivo);

            return $estancia->refresh();
        });
    }
}

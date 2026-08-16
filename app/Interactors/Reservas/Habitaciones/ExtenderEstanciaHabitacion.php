<?php

declare(strict_types=1);

namespace App\Interactors\Reservas\Habitaciones;

use App\BusinessLogic\Reservas\Data\ExtenderEstanciaData;
use App\BusinessLogic\Reservas\RecalcularTotalesReservaHabitacion;
use App\BusinessLogic\Reservas\ValidarDisponibilidadHabitacion;
use App\Enums\Estancias\EstadoEstancia;
use App\Events\Reservas\EstanciaHabitacionExtendida;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use DomainException;
use Illuminate\Support\Facades\DB;

final readonly class ExtenderEstanciaHabitacion
{
    public function __construct(
        private ValidarDisponibilidadHabitacion $validarDisponibilidad,
        private RecalcularTotalesReservaHabitacion $recalcularTotales,
        private ReservaRepositorioInterface $reservas,
    ) {}

    public function ejecutar(ExtenderEstanciaData $data): Estancia
    {
        return DB::transaction(function () use ($data): Estancia {
            $estancia = $this->reservas->estanciaConLock($data->estanciaId);

            if (! in_array($estancia->estado, [EstadoEstancia::ACTIVA, EstadoEstancia::EXTENDIDA], true)) {
                throw new DomainException("La estancia #{$estancia->id} no se encuentra activa para ser extendida.");
            }

            $detalle = $this->reservas->obtenerDetalleDeEstanciaConLock($estancia);
            if ($detalle === null) {
                throw new DomainException("La estancia #{$estancia->id} no tiene un detalle de reserva asociado.");
            }

            if ($detalle->fecha_fin === null) {
                throw new DomainException('La estancia no tiene una fecha de salida programada.');
            }

            if ($data->nuevaFechaSalida->lessThanOrEqualTo($detalle->fecha_fin)) {
                throw new DomainException('La nueva fecha de salida debe ser posterior a la fecha de salida actual.');
            }

            $anteriorSalida = $detalle->fecha_fin;

            // Re-validate availability for extended period excluding current detail
            $this->validarDisponibilidad->validarDisponibilidad(
                fechaCheckIn: $detalle->fecha_inicio,
                fechaCheckOut: $data->nuevaFechaSalida,
                recursosReservablesIds: [$detalle->reservable_id],
                adultos: $detalle->adultos,
                ninos: $detalle->ninos,
                excluirDetalleId: $detalle->id,
            );

            $this->reservas->actualizarDetalle($detalle, [
                'fecha_fin' => $data->nuevaFechaSalida,
            ]);

            $this->reservas->actualizarEstancia($estancia, [
                'fecha_salida_programada' => $data->nuevaFechaSalida,
                'estado' => EstadoEstancia::EXTENDIDA,
                'observaciones_salida' => $data->observaciones,
            ]);

            $reserva = $estancia->reserva;
            if ($reserva === null) {
                throw new DomainException('La estancia no tiene una reserva asociada.');
            }
            $this->recalcularTotales->ejecutar($reserva);

            EstanciaHabitacionExtendida::dispatch($estancia, $anteriorSalida, $data->nuevaFechaSalida);

            return $estancia->refresh();
        });
    }
}

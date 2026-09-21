<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Validaciones;

use App\BusinessLogic\Reservas\Data\ConsultarDisponibilidadHabitacionData;
use App\Repository\Queries\Reservas\ConsultarHabitacionesDisponibles;
use Carbon\CarbonInterface;
use DomainException;

final readonly class ValidarDisponibilidadHabitacion
{
    public function __construct(
        private ConsultarHabitacionesDisponibles $consultarHabitacionesDisponibles,
    ) {}

    public function estaDisponible(bool $existeConflicto): bool
    {
        return ! $existeConflicto;
    }

    /**
     * Valida que los recursos seleccionados estén disponibles para el período indicado.
     *
     * @param  array<int, int>  $recursosReservablesIds
     *
     * @throws DomainException Si la fecha fin no es posterior a fecha inicio o si algún recurso no está disponible.
     */
    public function validarDisponibilidad(
        CarbonInterface $fechaCheckIn,
        CarbonInterface $fechaCheckOut,
        array $recursosReservablesIds,
        int $adultos = 1,
        int $ninos = 0,
        ?int $excluirDetalleId = null,
    ): void {
        if ($fechaCheckOut->lessThanOrEqualTo($fechaCheckIn)) {
            throw new DomainException('La fecha de salida debe ser posterior a la fecha de entrada.');
        }

        if ($recursosReservablesIds === []) {
            throw new DomainException('Debe seleccionar al menos una habitación.');
        }

        $disponibles = $this->consultarHabitacionesDisponibles->ejecutar(
            new ConsultarDisponibilidadHabitacionData(
                fechaCheckIn: $fechaCheckIn,
                fechaCheckOut: $fechaCheckOut,
                adultos: $adultos,
                ninos: $ninos,
            ),
            excluirDetalleId: $excluirDetalleId,
        )->pluck('id')->all();

        foreach ($recursosReservablesIds as $recursoId) {
            if (! in_array($recursoId, $disponibles, true)) {
                throw new DomainException("La habitación solicitada (ID: {$recursoId}) no está disponible en las fechas seleccionadas.");
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\BusinessLogic\Huespedes;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoHuesped;
use App\Repository\Models\Habitaciones\DetalleHabitacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaHuesped;
use DomainException;

final class ValidarHuespedes
{
    public function __construct(
        private readonly ValidarTitularUnico $validarTitularUnico = new ValidarTitularUnico,
        private readonly ValidarDocumentacionHuesped $validarDocumentacion = new ValidarDocumentacionHuesped,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function validarCreacion(Reserva $reserva, array $datos): void
    {
        $this->validarEstadoReserva($reserva);
        $this->validarTitularUnico->validarRegistroNuevo($reserva, $datos);
        $this->validarDocumentacion->validar($datos);
        $this->validarCapacidad($reserva, $datos);
        $this->validarDuplicados($reserva, $datos);
        $this->validarCantidadesNoNegativas($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function validarActualizacion(Reserva $reserva, array $datos, ReservaHuesped $huesped): void
    {
        $this->validarEstadoReserva($reserva);
        $this->validarPertenencia($reserva, $huesped);
        $this->validarTitularUnico->validarRegistroNuevo($reserva, $datos, $huesped->id);
        $this->validarCapacidad($reserva, $datos, $huesped);
        $this->validarDocumentacion->validar($datos);
        $this->validarDuplicadosExcepto($reserva, $datos, $huesped->id);
        $this->validarCantidadesNoNegativas($datos);
    }

    public function validarEliminacion(Reserva $reserva, ReservaHuesped $huesped): void
    {
        $this->validarEstadoReserva($reserva);
        $this->validarPertenencia($reserva, $huesped);

        if ($huesped->es_titular) {
            throw new DomainException('No se puede eliminar el huésped titular. Asigne otro titular primero.');
        }
    }

    private function validarEstadoReserva(Reserva $reserva): void
    {
        if ($reserva->estado->value > EstadoReserva::CONFIRMADA->value) {
            throw new DomainException('No se pueden modificar huéspedes de una reserva que ya pasó el check-in.');
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function validarCapacidad(Reserva $reserva, array $datos, ?ReservaHuesped $actual = null): void
    {
        $reserva->loadMissing('detalles.reservable.habitacion.detalle', 'detalles.huespedes');
        $destino = $reserva->detalles->firstWhere('id', $datos['reserva_detalle_id'] ?? null);
        $habitacion = $destino?->reservable?->habitacion;
        if ($habitacion === null) {
            return;
        }

        /** @var DetalleHabitacion $detalle */
        $detalle = $habitacion->getRelation('detalle');

        $capacidadAdultos = (int) ($detalle->capacidad_adultos ?? 0);
        $capacidadNinos = (int) ($detalle->capacidad_ninos ?? 0);

        $adultosExistentes = $destino->huespedes->where('id', '!=', $actual?->id)
            ->where('tipo_huesped', TipoHuesped::ADULTO)
            ->count();
        $ninosExistentes = $destino->huespedes->where('id', '!=', $actual?->id)
            ->where('tipo_huesped', TipoHuesped::NINO)
            ->count();

        $nuevoAdulto = (($datos['tipo_huesped'] ?? null) === TipoHuesped::ADULTO->value || ($datos['tipo_huesped'] ?? null) === TipoHuesped::ADULTO);
        $nuevoNino = (($datos['tipo_huesped'] ?? null) === TipoHuesped::NINO->value || ($datos['tipo_huesped'] ?? null) === TipoHuesped::NINO);

        $totalAdultos = $adultosExistentes + ($nuevoAdulto ? 1 : 0);
        $totalNinos = $ninosExistentes + ($nuevoNino ? 1 : 0);

        if ($capacidadAdultos > 0 && $totalAdultos > $capacidadAdultos) {
            throw new DomainException("La habitación admite un máximo de {$capacidadAdultos} adultos. Actualmente hay {$adultosExistentes} registrados.");
        }

        if ($capacidadNinos >= 0 && $totalNinos > $capacidadNinos) {
            throw new DomainException("La habitación admite un máximo de {$capacidadNinos} niños. Actualmente hay {$ninosExistentes} registrados.");
        }

        $declarados = (int) $reserva->adultos + (int) $reserva->ninos;
        $totalRegistrados = $reserva->detalles->flatMap->huespedes->where('id', '!=', $actual?->id)->count() + 1;

        if ($reserva->detalles->count() === 1 && $totalRegistrados > $declarados) {
            throw new DomainException("La cantidad de huéspedes registrados ({$totalRegistrados}) supera la declarada en la reserva ({$declarados}).");
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function validarDuplicados(Reserva $reserva, array $datos): void
    {
        $this->verificarDuplicado($reserva, $datos, null);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function validarDuplicadosExcepto(Reserva $reserva, array $datos, int $huespedId): void
    {
        $this->verificarDuplicado($reserva, $datos, $huespedId);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function verificarDuplicado(Reserva $reserva, array $datos, ?int $exceptoId): void
    {
        $identificacion = $datos['identificacion'] ?? null;
        if (! is_string($identificacion) || $identificacion === '') {
            return;
        }

        $query = $reserva->huespedes()
            ->where('identificacion', $identificacion);

        if ($exceptoId !== null) {
            $query->where('reserva_huespedes.id', '!=', $exceptoId);
        }

        if ($query->exists()) {
            throw new DomainException("Ya existe un huésped con la identificación {$identificacion} en esta reserva.");
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function validarCantidadesNoNegativas(array $datos): void
    {
        // Cantidades validated at form level, this is a safety net
    }

    private function validarPertenencia(Reserva $reserva, ReservaHuesped $huesped): void
    {
        if ($huesped->detalle?->reserva_id !== $reserva->id) {
            throw new DomainException('El huesped no pertenece a la reserva indicada.');
        }
    }
}

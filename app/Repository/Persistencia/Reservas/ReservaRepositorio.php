<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\EstadoRecursoReservable;
use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoHuesped;
use App\Enums\Reservas\TipoRecursoReservable;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Servicios\Servicio;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReservaRepositorio implements ReservaRepositorioInterface
{
    public function crear(array $datos): Reserva
    {
        $reserva = Reserva::query()->create($datos);
        ReservaEstadoHistorial::query()->create([
            'reserva_id' => $reserva->id,
            'estado_anterior' => null,
            'estado_nuevo' => $reserva->estado,
            'motivo' => 'Reserva creada',
        ]);

        return $reserva;
    }

    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva
    {
        return DB::transaction(function () use ($reserva, $datos): Reserva {
            $bloqueada = Reserva::query()->lockForUpdate()->findOrFail($reserva->id);
            $bloqueada->update($datos);

            return $bloqueada->refresh();
        });
    }

    public function adjuntarServicios(Reserva $reserva, array $servicios): void
    {
        foreach ($servicios as $servicio) {
            $reserva->serviciosAdicionales()->attach($servicio['servicio_id'], [
                'cantidad' => $servicio['cantidad'],
                'precio' => $servicio['precio'],
            ]);
        }
    }

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable
    {
        [$entidad, $tipoRecurso, $control, $nombre, $capacidad] = match ($tipo) {
            TipoReserva::HABITACION => $this->datosHabitacion($entidadId),
            TipoReserva::RESTAURANTE => $this->datosEspacio($entidadId),
            TipoReserva::SERVICIO => $this->datosServicio($entidadId),
            TipoReserva::PAQUETE => throw new InvalidArgumentException('Los paquetes todavía no tienen recursos configurados.'),
        };

        if ($entidad->reservable_id !== null) {
            return RecursoReservable::query()->lockForUpdate()->findOrFail($entidad->reservable_id);
        }

        $recurso = RecursoReservable::query()->create([
            'tipo' => $tipoRecurso,
            'nombre' => $nombre,
            'capacidad' => $capacidad,
            'control_disponibilidad' => $control,
            'estado' => EstadoRecursoReservable::ACTIVO,
        ]);
        $entidad->update(['reservable_id' => $recurso->id]);

        return $recurso;
    }

    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle
    {
        return $reserva->detalles()->create([
            ...$datos,
            'reservable_id' => $recurso->id,
        ]);
    }

    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void
    {
        foreach ($huespedes as $huesped) {
            if (! is_array($huesped) || ! is_string($huesped['nombre'] ?? null)) {
                continue;
            }

            $tipo = match ($huesped['tipo'] ?? 'adulto') {
                'nino' => TipoHuesped::NINO,
                'infante' => TipoHuesped::INFANTE,
                default => TipoHuesped::ADULTO,
            };

            $detalle->huespedes()->create([
                'nombre' => $huesped['nombre'],
                'identificacion' => is_string($huesped['identificacion'] ?? null) ? $huesped['identificacion'] : null,
                'tipo_huesped' => $tipo,
                'es_titular' => (bool) ($huesped['es_titular'] ?? false),
            ]);
        }
    }

    public function cambiarEstado(Reserva $reserva, EstadoReserva $estado, ?int $usuarioId = null, ?string $motivo = null): void
    {
        DB::transaction(function () use ($reserva, $estado, $usuarioId, $motivo): void {
            $bloqueada = Reserva::query()->lockForUpdate()->findOrFail($reserva->id);
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => $estado]);

            $estadoDetalle = match ($estado) {
                EstadoReserva::PENDIENTE => EstadoReservaDetalle::PENDIENTE,
                EstadoReserva::CONFIRMADA => EstadoReservaDetalle::CONFIRMADO,
                EstadoReserva::CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_OUT => EstadoReservaDetalle::COMPLETADO,
                EstadoReserva::CANCELADA => EstadoReservaDetalle::CANCELADO,
            };
            $estadosOrigen = match ($estado) {
                EstadoReserva::PENDIENTE => [],
                EstadoReserva::CONFIRMADA => [EstadoReservaDetalle::PENDIENTE->value],
                EstadoReserva::CHECKED_IN => [EstadoReservaDetalle::CONFIRMADO->value],
                EstadoReserva::CHECKED_OUT => [EstadoReservaDetalle::EN_USO->value],
                EstadoReserva::CANCELADA => [EstadoReservaDetalle::PENDIENTE->value, EstadoReservaDetalle::CONFIRMADO->value],
            };

            if ($estadosOrigen !== []) {
                $bloqueada->detalles()->whereIn('estado', $estadosOrigen)->update(['estado' => $estadoDetalle->value]);
            }

            ReservaEstadoHistorial::query()->create([
                'reserva_id' => $bloqueada->id,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $estado,
                'motivo' => $motivo,
                'usuario_id' => $usuarioId,
            ]);

            $reserva->setRawAttributes($bloqueada->getAttributes(), true);
        });
    }

    /** @return array{Habitacion, TipoRecursoReservable, ControlDisponibilidad, string, int|null} */
    private function datosHabitacion(int $id): array
    {
        $habitacion = Habitacion::query()->lockForUpdate()->findOrFail($id);

        return [$habitacion, TipoRecursoReservable::HABITACION, ControlDisponibilidad::FECHAS, (string) $habitacion->nombre, null];
    }

    /** @return array{Espacio, TipoRecursoReservable, ControlDisponibilidad, string, int|null} */
    private function datosEspacio(int $id): array
    {
        $espacio = Espacio::query()->lockForUpdate()->findOrFail($id);

        return [$espacio, TipoRecursoReservable::ESPACIO, ControlDisponibilidad::HORARIO, (string) $espacio->nombre, (int) $espacio->capacidad_personas];
    }

    /** @return array{Servicio, TipoRecursoReservable, ControlDisponibilidad, string, null} */
    private function datosServicio(int $id): array
    {
        $servicio = Servicio::query()->lockForUpdate()->findOrFail($id);

        return [$servicio, TipoRecursoReservable::SERVICIO, ControlDisponibilidad::SIN_BLOQUEO, (string) $servicio->nombre, null];
    }
}

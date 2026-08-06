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
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaEstadoHistorial;
use App\Repository\Models\Reservas\ReservaHuesped;
use App\Repository\Models\Servicios\Servicio;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ReservaRepositorio implements ReservaRepositorioInterface
{
    public function obtenerPorId(int $id): ?Reserva
    {
        /** @var Reserva|null $reserva */
        $reserva = Reserva::query()->find($id);

        return $reserva;
    }

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

    public function actualizar(Reserva $reserva, array $datos): Reserva
    {
        $reserva->update($datos);

        return $reserva->refresh();
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

    /**
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     */
    public function reemplazarAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $servicios,
        array $espacios,
    ): void {
        $reserva->detalles()
            ->whereNotNull('parent_id')
            ->delete();

        $inicio = $principal->fecha_inicio;
        $fin = $principal->fecha_fin ?? $inicio;

        foreach ($servicios as $servicio) {
            $recurso = $this->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::PENDIENTE,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $servicio['cantidad'],
                'precio_unitario' => $servicio['precio'],
                'subtotal' => round($servicio['precio'] * $servicio['cantidad'], 2),
            ]);
        }

        foreach ($espacios as $espacio) {
            $recurso = $this->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $espacio['cantidad'],
                'precio_unitario' => $espacio['precio'],
                'subtotal' => round($espacio['precio'] * $espacio['cantidad'], 2),
            ]);
        }
    }

    /** @param array<string, mixed> $datos */
    public function actualizarDetalle(ReservaDetalle $detalle, array $datos): ReservaDetalle
    {
        $detalle->update($datos);

        return $detalle->refresh();
    }

    public function detallePrincipalDe(Reserva $reserva): ReservaDetalle
    {
        /** @var ReservaDetalle|null $detalle */
        $detalle = $reserva->detalles()->whereNull('parent_id')->first();

        if ($detalle instanceof ReservaDetalle) {
            return $detalle;
        }

        $recurso = $this->resolverRecursoPrincipalDeReserva($reserva);
        [$inicio, $fin] = $this->periodoPrincipalDeReserva($reserva, $recurso);

        /** @var ReservaDetalle $nuevo */
        $nuevo = $reserva->detalles()->create([
            'reservable_id' => $recurso->id,
            'estado' => match ($reserva->estado) {
                EstadoReserva::PENDIENTE => EstadoReservaDetalle::PENDIENTE,
                EstadoReserva::CONFIRMADA => EstadoReservaDetalle::CONFIRMADO,
                EstadoReserva::CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_OUT => EstadoReservaDetalle::COMPLETADO,
                EstadoReserva::CANCELADA => EstadoReservaDetalle::CANCELADO,
            },
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'adultos' => (int) $reserva->adultos,
            'ninos' => (int) $reserva->ninos,
            'precio_unitario' => (float) ($reserva->subtotal ?? $reserva->total ?? 0),
            'descuento' => (float) $reserva->descuento,
            'subtotal' => (float) ($reserva->subtotal ?? $reserva->total ?? 0),
            'notas' => $reserva->notas,
        ]);

        return $nuevo;
    }

    /** @param array<string, mixed> $datos */
    public function crearHuesped(ReservaDetalle $detalle, array $datos): ReservaHuesped
    {
        /** @var ReservaHuesped $huesped */
        $huesped = $detalle->huespedes()->create($datos);

        return $huesped;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarHuesped(ReservaHuesped $huesped, array $datos): ReservaHuesped
    {
        $huesped->update($datos);

        /** @var ReservaHuesped $actualizado */
        $actualizado = $huesped->fresh() ?? $huesped;

        return $actualizado;
    }

    public function eliminarHuesped(ReservaHuesped $huesped): void
    {
        $huesped->delete();
    }

    /** @param array<string, mixed> $datos */
    public function crearEstancia(array $datos): Estancia
    {
        return Estancia::query()->create($datos);
    }

    public function estanciaActivaDeReserva(Reserva $reserva): Estancia
    {
        /** @var Estancia $estancia */
        $estancia = Estancia::query()
            ->with('cuenta')
            ->whereBelongsTo($reserva)
            ->lockForUpdate()
            ->firstOrFail();

        return $estancia;
    }

    /** @param array<string, mixed> $datos */
    public function actualizarEstancia(Estancia $estancia, array $datos): Estancia
    {
        $estancia->update($datos);

        return $estancia->refresh();
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

    private function resolverRecursoPrincipalDeReserva(Reserva $reserva): RecursoReservable
    {
        return match ($reserva->tipo_reserva) {
            TipoReserva::HABITACION => $this->resolverRecursoDesdeId($reserva, TipoReserva::HABITACION, $reserva->habitacion_id, 'habitación'),
            TipoReserva::RESTAURANTE => $this->resolverRecursoDesdeId($reserva, TipoReserva::RESTAURANTE, $reserva->espacio_id, 'mesa/espacio'),
            TipoReserva::SERVICIO => $this->resolverRecursoDesdeId($reserva, TipoReserva::SERVICIO, $reserva->servicio_id, 'servicio'),
            TipoReserva::PAQUETE => throw new InvalidArgumentException('Los paquetes todavía no tienen recursos configurados.'),
        };
    }

    private function resolverRecursoDesdeId(Reserva $reserva, TipoReserva $tipo, mixed $id, string $nombreCampo): RecursoReservable
    {
        if (! is_numeric($id) || (int) $id <= 0) {
            throw new InvalidArgumentException("La reserva {$reserva->codigo_reserva} no tiene {$nombreCampo} principal asociado.");
        }

        return $this->resolverRecurso($tipo, (int) $id);
    }

    /** @return array{DateTimeImmutable, DateTimeImmutable} */
    private function periodoPrincipalDeReserva(Reserva $reserva, RecursoReservable $recurso): array
    {
        $fechaInicio = $reserva->fecha_check_in?->format('Y-m-d');
        if ($fechaInicio === null) {
            throw new InvalidArgumentException("La reserva {$reserva->codigo_reserva} no tiene fecha de inicio.");
        }

        $hora = trim((string) ($reserva->hora_reserva ?? ''));
        $inicio = new DateTimeImmutable($fechaInicio.' '.($hora !== '' ? $hora : '00:00'));

        if ($reserva->fecha_check_out !== null) {
            $fechaFin = $reserva->fecha_check_out->format('Y-m-d');
            $fin = new DateTimeImmutable($fechaFin.' '.($hora !== '' ? $hora : '23:59:59'));
        } else {
            $duracion = $recurso->duracion_minutos !== null && $recurso->duracion_minutos > 0
                ? $recurso->duracion_minutos
                : 60;
            $fin = $inicio->modify("+{$duracion} minutes");
        }

        if ($fin <= $inicio) {
            $fin = $inicio->modify('+1 hour');
        }

        return [$inicio, $fin];
    }
}

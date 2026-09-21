<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\EstadoReservaDetalle;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Queries\Reservas\ObtenerTarifasReservaQuery;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final readonly class ReservaDetalleRepositorio
{
    public function __construct(
        private ObtenerTarifasReservaQuery $tarifas,
        private RecursoReservableRepositorio $recursosRepo,
    ) {}

    public function obtenerDetalleConLock(int $detalleId): ReservaDetalle
    {
        /** @var ReservaDetalle $detalle */
        $detalle = ReservaDetalle::query()->where('id', $detalleId)->lockForUpdate()->firstOrFail();

        return $detalle;
    }

    public function obtenerReservaDeDetalleConLock(ReservaDetalle $detalle): Reserva
    {
        /** @var Reserva $reserva */
        $reserva = Reserva::query()->where('id', $detalle->reserva_id)->lockForUpdate()->firstOrFail();

        return $reserva;
    }

    public function obtenerDetalleDeReservaConLock(int $reservaId): ReservaDetalle
    {
        /** @var ReservaDetalle $detalle */
        $detalle = ReservaDetalle::query()->where('reserva_id', $reservaId)->lockForUpdate()->firstOrFail();

        return $detalle;
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesDe(Reserva $reserva): Collection
    {
        return $reserva->detalles()->get();
    }

    /** @return Collection<int, ReservaDetalle> */
    public function detallesPrincipalesDe(Reserva $reserva): Collection
    {
        return $reserva->detalles()->whereNull('parent_id')->get();
    }

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle
    {
        return $reserva->detalles()->create([
            ...$datos,
            'reservable_id' => $recurso->id,
        ]);
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
                EstadoReserva::PARCIALMENTE_CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_IN => EstadoReservaDetalle::EN_USO,
                EstadoReserva::PARCIALMENTE_CHECKED_OUT => EstadoReservaDetalle::EN_USO,
                EstadoReserva::CHECKED_OUT => EstadoReservaDetalle::COMPLETADO,
                EstadoReserva::CANCELADA => EstadoReservaDetalle::CANCELADO,
                EstadoReserva::NO_SHOW => EstadoReservaDetalle::CANCELADO,
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

    /**
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @param  array<int, array{habitacion_id: int, cantidad: int, precio: float}>  $habitaciones
     */
    public function reemplazarAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $servicios,
        array $espacios,
        array $habitaciones = [],
    ): void {
        $reserva->detalles()
            ->whereNotNull('parent_id')
            ->delete();

        $inicio = $principal->fecha_inicio;
        $fin = $principal->fecha_fin ?? $inicio;

        $todasLasSolicitudes = [];
        foreach ($habitaciones as $hab) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::HABITACION, 'entidad_id' => $hab['habitacion_id']];
        }
        foreach ($servicios as $servicio) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::SERVICIO, 'entidad_id' => $servicio['servicio_id']];
        }
        foreach ($espacios as $espacio) {
            $todasLasSolicitudes[] = ['tipo' => TipoReserva::RESTAURANTE, 'entidad_id' => $espacio['espacio_id']];
        }

        $recursos = $todasLasSolicitudes !== [] ? $this->recursosRepo->resolverRecursosLote($todasLasSolicitudes) : [];

        $idx = 0;

        foreach ($habitaciones as $hab) {
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::HABITACION, $hab['habitacion_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => 1,
                'precio_unitario' => $hab['precio'],
                'subtotal' => round($hab['precio'] * max(1, (int) $principal->cantidad), 2),
            ]);
        }

        foreach ($servicios as $servicio) {
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);

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
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);

            $horasVal = max(1, (int) $principal->cantidad);
            $mult = $this->tarifas->espacioEsPorHora($espacio['espacio_id']) ? $horasVal : 1;

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $espacio['cantidad'],
                'precio_unitario' => $espacio['precio'],
                'subtotal' => round($espacio['precio'] * $espacio['cantidad'] * $mult, 2),
            ]);
        }
    }

    /**
     * Crea detalles de habitaciones, servicios y espacios adicionales a partir de recursos resueltos.
     *
     * @param  array<int, RecursoReservable>  $recursos  Recursos resueltos en orden global
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     */
    public function crearDetallesAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $recursos,
        array $habitaciones,
        array $servicios,
        array $espacios,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        int $unidades,
        ?float $horasVal,
    ): void {
        $idx = 0;

        foreach ($habitaciones as $hab) {
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::HABITACION, $hab['habitacion_id']);

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => 1,
                'precio_unitario' => $hab['precio'],
                'subtotal' => round($hab['precio'] * $unidades, 2),
            ]);
        }

        foreach ($servicios as $servicio) {
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::SERVICIO, $servicio['servicio_id']);

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
            $recurso = $recursos[$idx++] ?? $this->recursosRepo->resolverRecurso(TipoReserva::RESTAURANTE, $espacio['espacio_id']);

            $mult = $this->tarifas->espacioEsPorHora($espacio['espacio_id']) ? max(1, (int) $horasVal) : 1;

            $this->crearDetalle($reserva, $recurso, [
                'parent_id' => $principal->id,
                'estado' => EstadoReservaDetalle::CONFIRMADO,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'cantidad' => $espacio['cantidad'],
                'precio_unitario' => $espacio['precio'],
                'subtotal' => round($espacio['precio'] * $espacio['cantidad'] * $mult, 2),
            ]);
        }
    }

    public function duracionHorasActual(Reserva $reserva): ?int
    {
        $detalles = $this->detallesPrincipalesDe($reserva);
        $principal = $detalles->first();

        if ($principal === null) {
            return null;
        }

        $fechaFin = $principal->fecha_fin;
        $fechaInicio = $principal->fecha_inicio;

        if ($fechaFin === null || $fechaInicio === null) { // @phpstan-ignore identical.alwaysFalse
            return null;
        }

        $horas = (int) ceil(($fechaFin->getTimestamp() - $fechaInicio->getTimestamp()) / 3600);

        return $horas > 0 ? $horas : 1;
    }

    private function resolverRecursoPrincipalDeReserva(Reserva $reserva): RecursoReservable
    {
        return match ($reserva->tipo_reserva) {
            TipoReserva::HABITACION => $this->resolverRecursoDesdeId($reserva, TipoReserva::HABITACION, $reserva->habitacion_id, 'habitación'),
            TipoReserva::RESTAURANTE => $this->resolverRecursoDesdeId($reserva, TipoReserva::RESTAURANTE, $reserva->espacio_id, 'mesa/espacio'),
            TipoReserva::SERVICIO => $this->resolverRecursoDesdeId($reserva, TipoReserva::SERVICIO, $reserva->servicio_id, 'servicio'),
            TipoReserva::PAQUETE => is_numeric($reserva->habitacion_id)
                ? $this->resolverRecursoDesdeId($reserva, TipoReserva::HABITACION, $reserva->habitacion_id, 'habitación')
                : (is_numeric($reserva->espacio_id)
                    ? $this->resolverRecursoDesdeId($reserva, TipoReserva::RESTAURANTE, $reserva->espacio_id, 'mesa/espacio')
                    : $this->resolverRecursoDesdeId($reserva, TipoReserva::SERVICIO, $reserva->servicio_id, 'servicio')),
        };
    }

    private function resolverRecursoDesdeId(Reserva $reserva, TipoReserva $tipo, mixed $id, string $nombreCampo): RecursoReservable
    {
        if (! is_numeric($id) || (int) $id <= 0) {
            throw new InvalidArgumentException("La reserva {$reserva->codigo_reserva} no tiene {$nombreCampo} principal asociado.");
        }

        return $this->recursosRepo->resolverRecurso($tipo, (int) $id);
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

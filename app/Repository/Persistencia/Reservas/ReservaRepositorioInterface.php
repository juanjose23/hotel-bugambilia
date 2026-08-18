<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Estancias\Estancia;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;
use App\Repository\Models\Reservas\ReservaHuesped;
use DateTimeImmutable;
use Illuminate\Support\Collection;

interface ReservaRepositorioInterface
{
    public function obtenerPorId(int $id): ?Reserva;

    public function obtenerPorIdConLock(int $id): Reserva;

    public function obtenerDetalleConLock(int $detalleId): ReservaDetalle;

    public function obtenerReservaDeDetalleConLock(ReservaDetalle $detalle): Reserva;

    public function obtenerReservaDeEstanciaConLock(Estancia $estancia): Reserva;

    public function obtenerDetalleDeEstanciaConLock(Estancia $estancia): ?ReservaDetalle;

    public function obtenerDetalleDeReservaConLock(int $reservaId): ReservaDetalle;

    public function estanciaConLock(int $estanciaId): Estancia;

    public function existeEstanciaActivaParaDetalle(int $detalleId): bool;

    public function tieneEstanciaActiva(Reserva $reserva): bool;

    public function obtenerRecursoConLock(int $recursoId): RecursoReservable;

    /** @return Collection<int, ReservaDetalle> */
    public function detallesDe(Reserva $reserva): Collection;

    /** @return Collection<int, ReservaDetalle> */
    public function detallesPrincipalesDe(Reserva $reserva): Collection;

    public function registrarHistorial(
        Reserva $reserva,
        ?EstadoReserva $anterior,
        EstadoReserva $nuevo,
        ?string $motivo = null,
        ?int $usuarioId = null,
    ): void;

    /** @param array<int, int> $ids */
    public function bloquearRecursosReservables(array $ids): void;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Reserva;

    /** @param array<string, mixed> $datos */
    public function actualizar(Reserva $reserva, array $datos): Reserva;

    /** @param array<string, mixed> $datos */
    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva;

    /** @param array<int, array{servicio_id: int, cantidad: int, precio: float}> $servicios */
    public function adjuntarServicios(Reserva $reserva, array $servicios): void;

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable;

    /**
     * Resuelve múltiples recursos en lote, ejecutando una sola query por tipo de entidad.
     *
     * @param  array<int, array{tipo: TipoReserva, entidad_id: int}>  $solicitudes
     * @return array<int, RecursoReservable> Indexed by original array key
     */
    public function resolverRecursosLote(array $solicitudes): array;

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle;

    /** @param array<int, mixed> $huespedes */
    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void;

    /**
     * Reemplaza los detalles hijos (servicios, espacios y habitaciones adicionales) de una reserva.
     *
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
    ): void;

    /** @param array<string, mixed> $datos */
    public function actualizarDetalle(ReservaDetalle $detalle, array $datos): ReservaDetalle;

    public function cambiarEstado(
        Reserva $reserva,
        EstadoReserva $estado,
        ?int $usuarioId = null,
        ?string $motivo = null,
    ): void;

    public function detallePrincipalDe(Reserva $reserva): ReservaDetalle;

    /** @param array<string, mixed> $datos */
    public function crearHuesped(ReservaDetalle $detalle, array $datos): ReservaHuesped;

    /** @param array<string, mixed> $datos */
    public function actualizarHuesped(ReservaHuesped $huesped, array $datos): ReservaHuesped;

    public function eliminarHuesped(ReservaHuesped $huesped): void;

    /** @param array<string, mixed> $datos */
    public function crearEstancia(array $datos): Estancia;

    public function estanciaActivaDeReserva(Reserva $reserva): Estancia;

    /** @param array<string, mixed> $datos */
    public function actualizarEstancia(Estancia $estancia, array $datos): Estancia;

    /**
     * Calcula la duración actual en horas de la reserva basándose en el detalle principal.
     *
     * Retorna null si no existe detalle principal o si no tiene fecha_fin definida.
     * El valor mínimo retornado es 1 hora.
     */
    public function duracionHorasActual(Reserva $reserva): ?int;

    /**
     * Crea detalles de habitaciones, servicios y espacios adicionales a partir de recursos resueltos.
     *
     * Optimizado para usar recursos pre-resueltos por ValidarDisponibilidadRecursoLote,
     * evitando queries adicionales de resolución.
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
    ): void;
}

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

interface ReservaRepositorioInterface
{
    public function obtenerPorId(int $id): ?Reserva;

    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Reserva;

    /** @param array<string, mixed> $datos */
    public function actualizar(Reserva $reserva, array $datos): Reserva;

    /** @param array<string, mixed> $datos */
    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva;

    /** @param array<int, array{servicio_id: int, cantidad: int, precio: float}> $servicios */
    public function adjuntarServicios(Reserva $reserva, array $servicios): void;

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable;

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle;

    /** @param array<int, mixed> $huespedes */
    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void;

    /**
     * Reemplaza los detalles hijos (servicios y espacios adicionales) de una reserva.
     *
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     */
    public function reemplazarAdicionales(
        Reserva $reserva,
        ReservaDetalle $principal,
        array $servicios,
        array $espacios,
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
}

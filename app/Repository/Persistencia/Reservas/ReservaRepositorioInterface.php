<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Reservas;

use App\Enums\Reservas\EstadoReserva;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\Reservas\ReservaDetalle;

interface ReservaRepositorioInterface
{
    /** @param array<string, mixed> $datos */
    public function crear(array $datos): Reserva;

    /** @param array<string, mixed> $datos */
    public function actualizarDatosGenerales(Reserva $reserva, array $datos): Reserva;

    /** @param array<int, array{servicio_id: int, cantidad: int, precio: float}> $servicios */
    public function adjuntarServicios(Reserva $reserva, array $servicios): void;

    public function resolverRecurso(TipoReserva $tipo, int $entidadId): RecursoReservable;

    /** @param array<string, mixed> $datos */
    public function crearDetalle(Reserva $reserva, RecursoReservable $recurso, array $datos): ReservaDetalle;

    /** @param array<int, mixed> $huespedes */
    public function crearHuespedes(ReservaDetalle $detalle, array $huespedes): void;

    public function cambiarEstado(
        Reserva $reserva,
        EstadoReserva $estado,
        ?int $usuarioId = null,
        ?string $motivo = null,
    ): void;
}

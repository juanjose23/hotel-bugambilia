<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Validaciones;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Models\Reservas\RecursoReservable;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Valida disponibilidad de recursos adicionales (habitaciones, servicios, espacios)
 * utilizando un pipeline batch optimizado que evita N+1 queries.
 */
final readonly class ValidarDisponibilidadRecursoLote
{
    public function __construct(
        private ReservaRepositorioInterface $reservas,
        private DisponibilidadRecursoQuery $disponibilidadRecursos,
    ) {}

    /**
     * Valida y resuelve recursos adicionales en lote.
     *
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @return array<int, RecursoReservable> Recursos resueltos indexados por posición global
     *
     * @throws InvalidArgumentException si algún recurso con control de disponibilidad tiene conflicto
     */
    public function ejecutar(
        array $habitaciones,
        array $servicios,
        array $espacios,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
        ?int $reservaExcluidaId = null,
    ): array {
        $solicitudes = $this->construirSolicitudes($habitaciones, $servicios, $espacios);

        if ($solicitudes === []) {
            return [];
        }

        $recursos = $this->reservas->resolverRecursosLote($solicitudes);
        $recursoIds = array_values(array_map(
            static fn (RecursoReservable $r): int => $r->id,
            $recursos,
        ));

        $this->reservas->bloquearRecursosReservables($recursoIds);

        $conflictos = $this->disponibilidadRecursos->existenConflictosLote($recursoIds, $inicio, $fin, $reservaExcluidaId);

        foreach ($recursos as $recurso) {
            if ($recurso->control_disponibilidad === ControlDisponibilidad::SIN_BLOQUEO) {
                continue;
            }

            if ($conflictos[$recurso->id] ?? false) {
                throw new InvalidArgumentException(
                    "El recurso {$recurso->nombre} no se encuentra disponible en las fechas y horas indicadas."
                );
            }
        }

        return $recursos;
    }

    /**
     * Construye la lista unificada de solicitudes respetando los índices globales.
     *
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @return array<int, array{tipo: TipoReserva, entidad_id: int}>
     */
    private function construirSolicitudes(array $habitaciones, array $servicios, array $espacios): array
    {
        $solicitudes = [];
        $pos = 0;

        foreach ($habitaciones as $h) {
            $solicitudes[$pos++] = [
                'tipo' => TipoReserva::HABITACION,
                'entidad_id' => (int) $h['habitacion_id'],
            ];
        }

        foreach ($servicios as $s) {
            $solicitudes[$pos++] = [
                'tipo' => TipoReserva::SERVICIO,
                'entidad_id' => (int) $s['servicio_id'],
            ];
        }

        foreach ($espacios as $e) {
            $solicitudes[$pos++] = [
                'tipo' => TipoReserva::RESTAURANTE,
                'entidad_id' => (int) $e['espacio_id'],
            ];
        }

        return $solicitudes;
    }
}

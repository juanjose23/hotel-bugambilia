<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

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
 *
 * Patrón:
 * 1. Construye array de solicitudes desde 3 listas de entrada
 * 2. Resuelve recursos en lote (una query por TipoReserva)
 * 3. Bloquea todos los recursos en batch (un solo SELECT FOR UPDATE)
 * 4. Verifica conflictos en batch (una query para todos)
 * 5. Valida individualmente con guard SIN_BLOQUEO
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

        $this->disponibilidadRecursos->bloquearRecursos($recursoIds);
        $conflictos = array_flip(
            $this->disponibilidadRecursos->existeConflictos($recursoIds, $inicio, $fin, $reservaExcluidaId),
        );

        $idx = 0;
        $this->validarCategoria($habitaciones, $recursos, $conflictos, $idx, 'habitación', 'no está disponible en las fechas indicadas.');
        $this->validarCategoria($servicios, $recursos, $conflictos, $idx, 'servicio', 'no está disponible en el horario especificado.');
        $this->validarCategoria($espacios, $recursos, $conflictos, $idx, 'espacio', 'no está disponible en el periodo solicitado.');

        return $recursos;
    }

    /**
     * Construye el array plano de solicitudes desde las 3 listas de entrada.
     *
     * @param  array<int, array{habitacion_id: int, precio: float}>  $habitaciones
     * @param  array<int, array{servicio_id: int, cantidad: int, precio: float}>  $servicios
     * @param  array<int, array{espacio_id: int, cantidad: int, precio: float}>  $espacios
     * @return array<int, array{tipo: TipoReserva, entidad_id: int}>
     */
    private function construirSolicitudes(array $habitaciones, array $servicios, array $espacios): array
    {
        $solicitudes = [];

        foreach ($habitaciones as $hab) {
            $solicitudes[] = ['tipo' => TipoReserva::HABITACION, 'entidad_id' => $hab['habitacion_id']];
        }
        foreach ($servicios as $servicio) {
            $solicitudes[] = ['tipo' => TipoReserva::SERVICIO, 'entidad_id' => $servicio['servicio_id']];
        }
        foreach ($espacios as $espacio) {
            $solicitudes[] = ['tipo' => TipoReserva::RESTAURANTE, 'entidad_id' => $espacio['espacio_id']];
        }

        return $solicitudes;
    }

    /**
     * Valida que los recursos de una categoría específica no tengan conflictos.
     *
     * El parámetro $idx se pasa por referencia para mantener el conteo
     * global entre las tres categorías (habitaciones → servicios → espacios).
     *
     * @param  array<int, mixed>  $items  Array original de la categoría
     * @param  array<int, RecursoReservable>  $recursos  Recursos resueltos en orden global
     * @param  array<int, int>  $conflictos  Mapa invertido de IDs con conflicto
     */
    private function validarCategoria(
        array $items,
        array $recursos,
        array $conflictos,
        int &$idx,
        string $tipoLabel,
        string $mensajeSuffix,
    ): void {
        if ($items === []) {
            return;
        }

        foreach ($items as $item) {
            $recurso = $recursos[$idx] ?? null;
            $idx++;

            if ($recurso === null) {
                continue;
            }

            if ($recurso->control_disponibilidad !== ControlDisponibilidad::SIN_BLOQUEO
                && isset($conflictos[$recurso->id])) {
                throw new InvalidArgumentException("El {$tipoLabel} {$recurso->nombre} {$mensajeSuffix}");
            }
        }
    }
}

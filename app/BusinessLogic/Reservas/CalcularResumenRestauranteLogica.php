<?php

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\ControlDisponibilidad;
use App\Enums\Reservas\TipoReserva;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Queries\Reservas\CalcularResumenRestauranteQuery;
use App\Repository\Queries\Reservas\DisponibilidadRecursoQuery;
use DateTimeImmutable;
use DomainException;

final readonly class CalcularResumenRestauranteLogica
{
    public function __construct(
        private CalcularResumenRestauranteQuery $calcularResumenRestaurante,
        private ReservaRepositorioInterface $reservas,
        private DisponibilidadRecursoQuery $disponibilidadRecursos,
    ) {}

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $itemsPreorden
     * @return array<string, mixed>
     */
    public function ejecutar(int $entidadPrincipalId, array $datos, array $espaciosAdicionales, array $itemsPreorden): array
    {
        $adultos = is_numeric($datos['adultos'] ?? null) ? (int) $datos['adultos'] : 1;
        $duracionHoras = is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : 1;

        $cobrarTarifaMesa = (bool) ($datos['cobrar_tarifa_mesa'] ?? false);

        $resumen = $this->calcularResumenRestaurante->ejecutar(
            mesaPrincipalId: $entidadPrincipalId,
            comensales: $adultos,
            horas: $duracionHoras,
            espaciosAdicionales: $espaciosAdicionales,
            itemsPreorden: $itemsPreorden,
            cobrarTarifaMesa: $cobrarTarifaMesa,
        );

        if ($resumen['capacidad_total'] < $adultos) {
            $this->lanzarCapacidadInsuficiente($resumen);
        }

        return $resumen;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $itemsPreorden
     * @return array<int, array{espacio_id: int, cantidad: int}>
     */
    public function completarEspaciosSugeridos(int $entidadPrincipalId, array $datos, array $espaciosAdicionales, array $itemsPreorden): array
    {
        $normalizados = $this->normalizarEspacios($espaciosAdicionales);
        $adultos = is_numeric($datos['adultos'] ?? null) ? (int) $datos['adultos'] : 1;

        for ($intento = 0; $intento < 10; $intento++) {
            $resumen = $this->ejecutarSinLanzar($entidadPrincipalId, $datos, $normalizados, $itemsPreorden);

            if ($resumen['capacidad_total'] >= $adultos) {
                return $normalizados;
            }

            if ($resumen['mesas_sugeridas'] === []) {
                $this->lanzarCapacidadInsuficiente($resumen);
            }

            $idsActuales = array_map(static fn (array $espacio): int => $espacio['espacio_id'], $normalizados);
            [$inicio, $fin] = $this->periodoReserva($datos);

            $mesasSugeridas = is_array($resumen['mesas_sugeridas'] ?? null) ? $resumen['mesas_sugeridas'] : [];
            foreach ($mesasSugeridas as $mesa) {
                if (! is_array($mesa) || ! is_numeric($mesa['id'] ?? null) || in_array((int) $mesa['id'], $idsActuales, true)) {
                    continue;
                }

                if (! $this->mesaDisponible((int) $mesa['id'], $inicio, $fin, $datos)) {
                    continue;
                }

                $normalizados[] = [
                    'espacio_id' => (int) $mesa['id'],
                    'cantidad' => 1,
                ];
                $idsActuales[] = (int) $mesa['id'];
            }
        }

        $this->lanzarCapacidadInsuficiente($this->ejecutarSinLanzar($entidadPrincipalId, $datos, $normalizados, $itemsPreorden));
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<int, mixed>  $espaciosAdicionales
     * @param  array<int, mixed>  $itemsPreorden
     * @return array<string, mixed>
     */
    private function ejecutarSinLanzar(int $entidadPrincipalId, array $datos, array $espaciosAdicionales, array $itemsPreorden): array
    {
        $adultos = is_numeric($datos['adultos'] ?? null) ? (int) $datos['adultos'] : 1;
        $duracionHoras = is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : 1;
        $cobrarTarifaMesa = (bool) ($datos['cobrar_tarifa_mesa'] ?? false);

        return $this->calcularResumenRestaurante->ejecutar(
            mesaPrincipalId: $entidadPrincipalId,
            comensales: $adultos,
            horas: $duracionHoras,
            espaciosAdicionales: $espaciosAdicionales,
            itemsPreorden: $itemsPreorden,
            cobrarTarifaMesa: $cobrarTarifaMesa,
        );
    }

    /**
     * @param  array<int, mixed>  $espaciosAdicionales
     * @return array<int, array{espacio_id: int, cantidad: int}>
     */
    private function normalizarEspacios(array $espaciosAdicionales): array
    {
        $normalizados = [];

        foreach ($espaciosAdicionales as $espacio) {
            if (! is_array($espacio) || ! is_numeric($espacio['espacio_id'] ?? null)) {
                continue;
            }

            $normalizados[] = [
                'espacio_id' => (int) $espacio['espacio_id'],
                'cantidad' => 1,
            ];
        }

        return array_values(array_unique($normalizados, SORT_REGULAR));
    }

    /** @param array<string, mixed> $resumen */
    private function lanzarCapacidadInsuficiente(array $resumen): never
    {
        $mesasRequeridas = is_numeric($resumen['mesas_requeridas'] ?? null) ? (int) $resumen['mesas_requeridas'] : 0;
        $mesasSeleccionadas = is_numeric($resumen['mesas_seleccionadas'] ?? null) ? (int) $resumen['mesas_seleccionadas'] : 0;
        $faltantes = max(0, $mesasRequeridas - $mesasSeleccionadas);
        $mesasSugeridasArray = is_array($resumen['mesas_sugeridas'] ?? null) ? $resumen['mesas_sugeridas'] : [];
        $mesasSugeridas = implode(' + ', array_column($mesasSugeridasArray, 'nombre'));
        $sugerencia = $mesasSugeridas !== ''
            ? " Mesas sugeridas para unir: $mesasSugeridas."
            : ' No existen mesas adicionales suficientes para completar la capacidad.';

        throw new DomainException("La capacidad seleccionada es insuficiente. Debe agregar $faltantes mesa(s) adicional(es) para atender a todos los comensales.$sugerencia");
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}
     */
    private function periodoReserva(array $datos): array
    {
        $fecha = is_string($datos['fecha_check_in'] ?? null) ? $datos['fecha_check_in'] : 'today';
        $hora = is_string($datos['hora_reserva'] ?? null) && trim((string) $datos['hora_reserva']) !== ''
            ? trim((string) $datos['hora_reserva'])
            : '00:00';
        $horas = max(1, is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : 1);
        $inicio = new DateTimeImmutable($fecha.' '.$hora);

        return [$inicio, $inicio->modify("+{$horas} hours")];
    }

    /** @param array<string, mixed> $datos */
    private function mesaDisponible(int $mesaId, DateTimeImmutable $inicio, DateTimeImmutable $fin, array $datos): bool
    {
        $recurso = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $mesaId);
        $reservaExcluidaId = is_numeric($datos['reserva_id'] ?? null) ? (int) $datos['reserva_id'] : null;

        return $recurso->control_disponibilidad === ControlDisponibilidad::SIN_BLOQUEO
            || ! $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin, $reservaExcluidaId);
    }
}

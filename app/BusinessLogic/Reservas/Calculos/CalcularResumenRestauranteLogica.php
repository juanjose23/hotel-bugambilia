<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas\Calculos;

use App\BusinessLogic\Reservas\Data\EspacioAdicionalItemData;
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

        $cobrarTarifaMesa = isset($datos['cobrar_tarifa_mesa']) ? (bool) $datos['cobrar_tarifa_mesa'] : true;

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

        if ($entidadPrincipalId <= 0 || $adultos <= 1) {
            return $normalizados;
        }

        $duracionHoras = is_numeric($datos['duracion_horas'] ?? null) ? (int) $datos['duracion_horas'] : 1;
        $cobrarTarifaMesa = isset($datos['cobrar_tarifa_mesa']) ? (bool) $datos['cobrar_tarifa_mesa'] : true;

        $resumen = $this->calcularResumenRestaurante->ejecutar(
            mesaPrincipalId: $entidadPrincipalId,
            comensales: $adultos,
            horas: $duracionHoras,
            espaciosAdicionales: $normalizados,
            itemsPreorden: $itemsPreorden,
            cobrarTarifaMesa: $cobrarTarifaMesa,
        );

        $capacidadTotal = $resumen['capacidad_total'];
        if ($capacidadTotal >= $adultos) {
            return $normalizados;
        }

        $mesasSugeridas = $resumen['mesas_sugeridas'];
        if ($mesasSugeridas === []) {
            return $normalizados;
        }

        $fechaRaw = $datos['fecha_check_in'] ?? null;
        $fechaStr = is_string($fechaRaw) ? $fechaRaw : ($fechaRaw instanceof \DateTimeInterface ? $fechaRaw->format('Y-m-d') : 'now');
        $checkIn = new DateTimeImmutable($fechaStr);
        $horaReserva = is_string($datos['hora_reserva'] ?? null) ? trim($datos['hora_reserva']) : '00:00';
        $inicio = new DateTimeImmutable($checkIn->format('Y-m-d').' '.$horaReserva);
        $fin = $inicio->modify('+'.max(1, $duracionHoras).' hours');

        $idsYaIncluidos = array_map(
            static fn (array $item): int => (int) $item['espacio_id'],
            $normalizados,
        );
        $idsYaIncluidos[] = $entidadPrincipalId;

        foreach ($mesasSugeridas as $mesaSugerida) {
            $idMesa = $mesaSugerida['id'];
            if ($idMesa <= 0 || in_array($idMesa, $idsYaIncluidos, true)) {
                continue;
            }

            $capacidadMesa = $mesaSugerida['capacidad'];

            if ($this->mesaEstaDisponible($idMesa, $inicio, $fin)) {
                $normalizados[] = [
                    'espacio_id' => $idMesa,
                    'cantidad' => 1,
                ];
                $idsYaIncluidos[] = $idMesa;
                $capacidadTotal += $capacidadMesa;

                if ($capacidadTotal >= $adultos) {
                    break;
                }
            }
        }

        return $normalizados;
    }

    /**
     * @param  array{
     *     capacidad_total: int,
     *     mesas_sugeridas: array<int, array{id: int, nombre: string, capacidad: int}>,
     *     ...
     * }  $resumen
     */
    private function lanzarCapacidadInsuficiente(array $resumen): void
    {
        $mesasSugeridas = $resumen['mesas_sugeridas'];
        $capacidadTotal = (string) $resumen['capacidad_total'];

        if ($mesasSugeridas !== []) {
            /** @var list<string> $nombresSugeridosArray */
            $nombresSugeridosArray = [];
            foreach ($mesasSugeridas as $mesa) {
                $nombresSugeridosArray[] = $mesa['nombre'] !== '' ? $mesa['nombre'] : "Mesa #{$mesa['id']}";
            }
            $nombresSugeridos = implode(', ', $nombresSugeridosArray);

            throw new DomainException(
                "La capacidad total ({$capacidadTotal} personas) no es suficiente. "
                ."Se sugiere agregar las siguientes mesas del mismo ambiente para completar la capacidad: {$nombresSugeridos}."
            );
        }

        throw new DomainException(
            "La capacidad total de las mesas seleccionadas ({$capacidadTotal} personas) "
            .'es insuficiente para el número de comensales y no hay mesas adicionales disponibles en este ambiente.'
        );
    }

    /**
     * @param  array<int, mixed>  $espacios
     * @return array<int, array{espacio_id: int, cantidad: int}>
     */
    private function normalizarEspacios(array $espacios): array
    {
        $resultado = [];

        foreach ($espacios as $espacio) {
            if ($espacio instanceof EspacioAdicionalItemData) {
                if ($espacio->espacioId > 0) {
                    $resultado[] = [
                        'espacio_id' => $espacio->espacioId,
                        'cantidad' => $espacio->cantidad,
                    ];
                }

                continue;
            }

            if (! is_array($espacio)) {
                continue;
            }

            $id = $espacio['espacio_id'] ?? $espacio['id'] ?? null;
            if (! is_numeric($id) || (int) $id <= 0) {
                continue;
            }

            $cantidad = is_numeric($espacio['cantidad'] ?? null) ? max(1, (int) $espacio['cantidad']) : 1;

            $resultado[] = [
                'espacio_id' => (int) $id,
                'cantidad' => $cantidad,
            ];
        }

        return $resultado;
    }

    private function mesaEstaDisponible(int $mesaId, DateTimeImmutable $inicio, DateTimeImmutable $fin): bool
    {
        $recurso = $this->reservas->resolverRecurso(TipoReserva::RESTAURANTE, $mesaId);

        if ($recurso->control_disponibilidad === ControlDisponibilidad::SIN_BLOQUEO) {
            return true;
        }

        return ! $this->disponibilidadRecursos->existeConflicto($recurso->id, $inicio, $fin);
    }
}

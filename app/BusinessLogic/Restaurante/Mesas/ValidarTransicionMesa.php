<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Mesas;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Restaurante\MotivoTransicionMesa;
use DomainException;

final class ValidarTransicionMesa
{
    /** @return EstadoEspacio[] */
    private function permitidos(EstadoEspacio $actual, MotivoTransicionMesa $motivo): array
    {
        if ($motivo !== MotivoTransicionMesa::Manual) {
            return $this->permitidosPorFlujo($actual, $motivo);
        }

        return match ($actual) {
            EstadoEspacio::Disponible => [EstadoEspacio::Mantenimiento, EstadoEspacio::Reservado, EstadoEspacio::Inactivo, EstadoEspacio::Sucio, EstadoEspacio::Limpieza],
            EstadoEspacio::Ocupado => [EstadoEspacio::Sucio, EstadoEspacio::Limpieza],
            EstadoEspacio::Sucio => [EstadoEspacio::Limpieza],
            EstadoEspacio::Limpieza => [EstadoEspacio::Sucio],
            EstadoEspacio::Mantenimiento => [EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
            EstadoEspacio::Reservado => [EstadoEspacio::Sucio, EstadoEspacio::Limpieza],
            EstadoEspacio::Inactivo => [EstadoEspacio::Disponible],
        };
    }

    public function validar(
        EstadoEspacio $actual,
        EstadoEspacio $nuevo,
        MotivoTransicionMesa $motivo = MotivoTransicionMesa::Manual,
    ): void {
        if ($actual === $nuevo) {
            return;
        }

        $permitidos = $this->permitidos($actual, $motivo);

        if (! in_array($nuevo, $permitidos, true)) {
            throw new DomainException(
                "Transición no permitida: de '{$actual->getLabel()}' a '{$nuevo->getLabel()}' por motivo '{$motivo->value}'."
            );
        }
    }

    /** @return EstadoEspacio[] */
    private function permitidosPorFlujo(EstadoEspacio $actual, MotivoTransicionMesa $motivo): array
    {
        return match ($motivo) {
            MotivoTransicionMesa::AperturaPedido,
            MotivoTransicionMesa::UnionMesas => $actual === EstadoEspacio::Disponible
                ? [EstadoEspacio::Ocupado]
                : [],

            MotivoTransicionMesa::MovimientoCuenta => $actual === EstadoEspacio::Disponible
                ? [EstadoEspacio::Ocupado]
                : ($actual === EstadoEspacio::Ocupado ? [EstadoEspacio::Disponible] : []),

            MotivoTransicionMesa::LlegadaReserva => $actual === EstadoEspacio::Reservado
                ? [EstadoEspacio::Ocupado]
                : [],

            MotivoTransicionMesa::CierrePedido => $actual === EstadoEspacio::Ocupado
                ? [EstadoEspacio::Sucio, EstadoEspacio::Limpieza]
                : [],

            MotivoTransicionMesa::CancelacionReserva => in_array($actual, [EstadoEspacio::Reservado, EstadoEspacio::Ocupado], true)
                ? [EstadoEspacio::Disponible, EstadoEspacio::Sucio, EstadoEspacio::Limpieza]
                : [],

            MotivoTransicionMesa::SeparacionMesas => $actual === EstadoEspacio::Ocupado
                ? [EstadoEspacio::Disponible]
                : [],

            MotivoTransicionMesa::LimpiezaIniciada => $actual === EstadoEspacio::Sucio
                ? [EstadoEspacio::Limpieza]
                : [],

            MotivoTransicionMesa::LimpiezaCompletada => $actual === EstadoEspacio::Limpieza
                ? [EstadoEspacio::Disponible, EstadoEspacio::Sucio]
                : [],

            MotivoTransicionMesa::Administracion => match ($actual) {
                EstadoEspacio::Disponible => [EstadoEspacio::Mantenimiento, EstadoEspacio::Inactivo],
                EstadoEspacio::Mantenimiento => [EstadoEspacio::Disponible, EstadoEspacio::Inactivo],
                EstadoEspacio::Inactivo => [EstadoEspacio::Disponible, EstadoEspacio::Mantenimiento],
                default => [],
            },

            MotivoTransicionMesa::Manual => [],
        };
    }
}

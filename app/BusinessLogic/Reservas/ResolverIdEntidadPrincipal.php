<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoReserva;
use InvalidArgumentException;

/**
 * Determina el ID de la entidad principal de la reserva según el tipo y el payload.
 * Cada TipoReserva mapea a un campo específico del payload.
 */
final readonly class ResolverIdEntidadPrincipal
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function resolver(TipoReserva $tipo, array $datos): int
    {
        return match ($tipo) {
            TipoReserva::HABITACION => $this->enteroRequerido($datos, 'habitacion_id'),
            TipoReserva::RESTAURANTE => $this->enteroRequerido($datos, 'espacio_id'),
            TipoReserva::SERVICIO => $this->enteroRequerido($datos, 'servicio_id'),
            TipoReserva::PAQUETE => is_numeric($datos['habitacion_id'] ?? null)
                ? (int) $datos['habitacion_id']
                : (is_numeric($datos['espacio_id'] ?? null)
                    ? (int) $datos['espacio_id']
                    : (is_numeric($datos['servicio_id'] ?? null)
                        ? (int) $datos['servicio_id']
                        : $this->enteroOpcional($datos, 'paquete_id', 0))),
        };
    }

    /** @param array<string|int, mixed> $datos */
    private function enteroRequerido(array $datos, string $campo): int
    {
        $valor = $datos[$campo] ?? null;

        if (is_int($valor)) {
            return $valor;
        }

        if (is_string($valor) && ctype_digit($valor)) {
            return (int) $valor;
        }

        throw new InvalidArgumentException("El campo $campo no es válido.");
    }

    /** @param array<string, mixed> $datos */
    private function enteroOpcional(array $datos, string $campo, int $predeterminado): int
    {
        $valor = $datos[$campo] ?? $predeterminado;

        return is_numeric($valor) ? (int) $valor : $predeterminado;
    }
}

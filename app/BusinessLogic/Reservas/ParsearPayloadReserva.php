<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoReserva;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Valida y castea el payload crudo del request de reserva a tipos seguros.
 * Responsabilidad única: garantizar que los datos de entrada sean válidos antes
 * de que el Interactor los procese.
 */
final readonly class ParsearPayloadReserva
{
    /**
     * @param  array<string, mixed>  $datos
     * @return array{tipo: TipoReserva, checkIn: DateTimeImmutable, checkOut: ?DateTimeImmutable, horaReserva: ?string, itemsPreorden: array<mixed>}
     */
    public function parsear(array $datos): array
    {
        $tipoValor = $datos['tipo_reserva'] ?? null;
        $checkInValor = $datos['fecha_check_in'] ?? null;
        $checkOutValor = $datos['fecha_check_out'] ?? null;

        if (! is_string($tipoValor) || ! is_string($checkInValor)) {
            throw new InvalidArgumentException('Los datos de la reserva no son válidos.');
        }

        return [
            'tipo' => TipoReserva::from($tipoValor),
            'checkIn' => new DateTimeImmutable($checkInValor),
            'checkOut' => is_string($checkOutValor) ? new DateTimeImmutable($checkOutValor) : null,
            'horaReserva' => is_string($datos['hora_reserva'] ?? null) ? trim((string) $datos['hora_reserva']) : null,
            'itemsPreorden' => is_array($datos['items_preorden'] ?? null) ? $datos['items_preorden'] : [],
        ];
    }
}

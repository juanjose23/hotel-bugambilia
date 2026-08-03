<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use DateTimeImmutable;

final class CalcularPeriodoReserva
{
    /**
     * Resuelve las marcas de tiempo de inicio ($inicio) y fin ($fin) de una reserva.
     *
     * @param  array<string, mixed>  $datos
     * @return array{DateTimeImmutable, DateTimeImmutable}
     */
    public function calcular(
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        array $datos,
        ?int $duracionMinutos,
    ): array {
        $hora = is_string($datos['hora_reserva'] ?? null) ? $datos['hora_reserva'] : '00:00';
        $inicio = new DateTimeImmutable($checkIn->format('Y-m-d').' '.$hora);

        $horaFin = is_string($datos['hora_fin'] ?? null) ? $datos['hora_fin'] : null;

        if ($horaFin !== null && $horaFin !== '') {
            $fin = new DateTimeImmutable($checkIn->format('Y-m-d').' '.$horaFin);
            if ($fin <= $inicio) {
                $fin = $fin->modify('+1 day');
            }
        } elseif ($checkOut !== null) {
            $fin = new DateTimeImmutable($checkOut->format('Y-m-d').' 00:00');
        } elseif (is_numeric($datos['duracion_horas'] ?? null)) {
            $fin = $inicio->modify('+'.max(1, (int) $datos['duracion_horas']).' hours');
        } else {
            $fin = $inicio->modify('+'.max(1, $duracionMinutos ?? 60).' minutes');
        }

        return [$inicio, $fin];
    }
}

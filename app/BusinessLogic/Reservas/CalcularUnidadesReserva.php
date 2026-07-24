<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Reservas\TipoReserva;
use DateTimeImmutable;

final class CalcularUnidadesReserva
{
    /**
     * Calcula la cantidad de unidades de cobro (días para habitaciones, horas redondeadas para alquiler de espacios, 1 para fijos).
     */
    public function calcular(
        TipoReserva $tipo,
        DateTimeImmutable $checkIn,
        ?DateTimeImmutable $checkOut,
        bool $esPorHora,
        DateTimeImmutable $inicio,
        DateTimeImmutable $fin,
    ): int {
        if ($tipo === TipoReserva::HABITACION) {
            $salida = $checkOut ?? $checkIn->modify('+1 day');

            return max(1, (int) $checkIn->diff($salida)->days);
        }

        if ($tipo === TipoReserva::RESTAURANTE && $esPorHora) {
            $diferenciaSegundos = max(0, $fin->getTimestamp() - $inicio->getTimestamp());

            return max(1, (int) ceil($diferenciaSegundos / 3600));
        }

        return 1;
    }
}

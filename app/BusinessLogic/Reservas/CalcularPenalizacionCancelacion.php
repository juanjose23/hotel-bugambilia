<?php

declare(strict_types=1);

namespace App\BusinessLogic\Reservas;

use App\Enums\Politicas\UnidadAnticipacion;
use App\Repository\Models\Politicas\PoliticaPenalizacion;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Queries\Politicas\ResolverPoliticaCancelacionReservaQuery;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class ResultadoPenalizacion
{
    public function __construct(
        public float $porcentaje,
        public float $monto,
    ) {}
}

final class CalcularPenalizacionCancelacion
{
    public function __construct(
        private readonly ResolverPoliticaCancelacionReservaQuery $resolverPolitica,
    ) {}

    public function ejecutar(
        Reserva $reserva,
        DateTimeInterface $fechaCancelacion,
        bool $esNoShow = false,
    ): ResultadoPenalizacion {
        $totalReserva = (float) $reserva->total;
        if ($totalReserva <= 0.0) {
            return new ResultadoPenalizacion(0.0, 0.0);
        }

        $politica = $this->resolverPolitica->ejecutar($reserva);
        if ($politica === null || $politica->penalizaciones->isEmpty()) {
            return new ResultadoPenalizacion(0.0, 0.0);
        }

        $fechaInicio = $this->resolverFechaInicio($reserva);
        $dtCancelacion = DateTimeImmutable::createFromInterface($fechaCancelacion);

        if ($esNoShow) {
            /** @var PoliticaPenalizacion|null $rangoNoShow */
            $rangoNoShow = $politica->penalizaciones
                ->where('aplica_no_show', true)
                ->first();

            $porcentaje = $rangoNoShow !== null ? (float) $rangoNoShow->porcentaje : 100.0;
            $monto = round(($porcentaje / 100.0) * $totalReserva, 2);

            return new ResultadoPenalizacion($porcentaje, $monto);
        }

        // Anticipación antes del check-in / servicio
        $diasAnticipacion = max(0, (int) floor(($fechaInicio->getTimestamp() - $dtCancelacion->getTimestamp()) / 86400));
        $horasAnticipacion = max(0, (int) floor(($fechaInicio->getTimestamp() - $dtCancelacion->getTimestamp()) / 3600));

        foreach ($politica->penalizaciones as $rango) {
            if ($rango->aplica_no_show) {
                continue;
            }

            $anticipacion = $rango->unidad === UnidadAnticipacion::DIAS ? $diasAnticipacion : $horasAnticipacion;
            $min = $rango->min_unidades !== null ? (int) $rango->min_unidades : 0;
            $max = $rango->max_unidades !== null ? (int) $rango->max_unidades : PHP_INT_MAX;

            if ($anticipacion >= $min && $anticipacion <= $max) {
                $porcentaje = (float) $rango->porcentaje;
                $monto = round(($porcentaje / 100.0) * $totalReserva, 2);

                return new ResultadoPenalizacion($porcentaje, $monto);
            }
        }

        if ($dtCancelacion >= $fechaInicio) {
            /** @var PoliticaPenalizacion|null $rangoNoShow */
            $rangoNoShow = $politica->penalizaciones
                ->where('aplica_no_show', true)
                ->first();

            $porcentaje = $rangoNoShow !== null ? (float) $rangoNoShow->porcentaje : 100.0;
            $monto = round(($porcentaje / 100.0) * $totalReserva, 2);

            return new ResultadoPenalizacion($porcentaje, $monto);
        }

        // Sin rango que aplique -> cancelación gratuita
        return new ResultadoPenalizacion(0.0, 0.0);
    }

    private function resolverFechaInicio(Reserva $reserva): DateTimeImmutable
    {
        if ($reserva->fecha_check_in !== null) {
            return DateTimeImmutable::createFromInterface($reserva->fecha_check_in)->setTime(14, 0, 0);
        }

        return new DateTimeImmutable('now');
    }
}

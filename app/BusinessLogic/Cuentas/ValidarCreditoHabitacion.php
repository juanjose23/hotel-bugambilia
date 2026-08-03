<?php

declare(strict_types=1);

namespace App\BusinessLogic\Cuentas;

use App\Enums\Estancias\EstadoEstancia;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Estancias\Estancia;
use DomainException;

final class ValidarCreditoHabitacion
{
    public function validar(?Estancia $estancia, ?Cuenta $cuentaEstancia, float $montoNuevoConsumo): void
    {
        if ($estancia === null) {
            throw new DomainException('No existe una estancia registrada para la habitación seleccionada.');
        }

        if ($estancia->estado !== EstadoEstancia::ACTIVA) {
            throw new DomainException('La habitación seleccionada no cuenta con una estancia activa (Checked-In).');
        }

        $reserva = $estancia->reserva;
        if ($reserva !== null && ! $reserva->solicita_cuenta) {
            throw new DomainException('La reserva de la habitación no tiene autorizada la apertura de cuenta de consumos.');
        }

        if ($cuentaEstancia !== null && $cuentaEstancia->limite_autorizado !== null) {
            $limite = (float) $cuentaEstancia->limite_autorizado;
            $saldoActual = (float) $cuentaEstancia->saldo;
            $totalConNuevoConsumo = $saldoActual + $montoNuevoConsumo;

            if ($totalConNuevoConsumo > $limite) {
                $exceso = round($totalConNuevoConsumo - $limite, 2);
                throw new DomainException("El monto del consumo (C$ {$montoNuevoConsumo}) excede el límite de crédito autorizado de la habitación (Límite: C$ {$limite}, Saldo actual: C$ {$saldoActual}, Exceso: C$ {$exceso}).");
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante\Validaciones;

use App\Support\MonedaHelper;
use DomainException;

final class ValidarMontoSuficienteCobro
{
    public function validar(float $montoRecibido, float $montoRequerido, ?string $monedaSimbolo = null): void
    {
        if ($montoRecibido < $montoRequerido) {
            $simbolo = $monedaSimbolo ?? MonedaHelper::simbolo();
            throw new DomainException(
                "El monto recibido ({$simbolo} {$montoRecibido}) es menor al subtotal ({$simbolo} {$montoRequerido})."
            );
        }
    }
}

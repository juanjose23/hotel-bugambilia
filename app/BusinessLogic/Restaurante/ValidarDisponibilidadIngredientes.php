<?php

declare(strict_types=1);

namespace App\BusinessLogic\Restaurante;

use DomainException;

final class ValidarDisponibilidadIngredientes
{
    public function validar(float $requerido, float $disponible, string $ingrediente): void
    {
        if ($disponible < $requerido) {
            throw new DomainException("Stock insuficiente para {$ingrediente}. Disponible: {$disponible}; requerido: {$requerido}.");
        }
    }
}

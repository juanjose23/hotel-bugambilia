<?php

declare(strict_types=1);

namespace App\BusinessLogic\Compras;

use DomainException;

final class ValidarCancelacionOrden
{
    public function validar(bool $tieneRecepciones): void
    {
        if ($tieneRecepciones) {
            throw new DomainException(
                'No se puede cancelar una orden que ya tiene recepciones registradas.'
            );
        }
    }
}

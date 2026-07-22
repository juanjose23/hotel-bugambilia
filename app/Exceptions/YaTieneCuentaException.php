<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Repository\Models\Personas\Persona;

final class YaTieneCuentaException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly Persona $persona,
    ) {
        parent::__construct($message);
    }
}

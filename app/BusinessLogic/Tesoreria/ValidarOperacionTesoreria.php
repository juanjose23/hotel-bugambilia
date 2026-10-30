<?php

declare(strict_types=1);

namespace App\BusinessLogic\Tesoreria;

use DomainException;
use Illuminate\Support\Str;

final readonly class ValidarOperacionTesoreria
{
    public function __construct(private CalcularArqueo $calculo) {}

    public function importe(float $importe): int
    {
        $c = $this->calculo->centavos($importe);
        if ($c <= 0) {
            throw new DomainException('El importe debe ser positivo y tener como máximo dos decimales.');
        }

        return $c;
    }

    public function texto(string $texto, int $min = 10, int $max = 1000): string
    {
        $texto = trim($texto);
        if (mb_strlen($texto) < $min || mb_strlen($texto) > $max) {
            throw new DomainException("Escribe entre $min y $max caracteres.");
        }

        return $texto;
    }

    public function referencia(string $ref): string
    {
        $ref = mb_strtoupper(trim($ref));

        return $this->texto($ref, 3, 150);
    }

    public function clave(string $clave): void
    {
        if (! Str::isUuid($clave)) {
            throw new DomainException('La operación necesita una referencia válida.');
        }
    }

    public function fecha(string $fecha): string
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (! $d || $d->format('Y-m-d') !== $fecha || $fecha > date('Y-m-d')) {
            throw new DomainException('La fecha del movimiento debe ser válida y no futura.');
        }

        return $fecha;
    }
}

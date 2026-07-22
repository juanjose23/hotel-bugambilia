<?php

declare(strict_types=1);

namespace App\Support\Pdf\Calculadores;

interface CalculadorAltura
{
    public function altura(mixed $item): int;
}

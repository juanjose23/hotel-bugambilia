<?php

declare(strict_types=1);

namespace App\Support\Pdf\Calculadores;

class CalculadorTarjetas implements CalculadorAltura
{
    public function altura(mixed $item): int
    {
        $lineas = 0;

        if (is_object($item)) {
            $detalles = data_get($item, 'detalles', []);
            $arr = is_array($detalles) ? $detalles : [];
            $lineas = count($arr);
        }

        if ($lineas <= 5) {
            return 20;
        }

        return 20 + (int) (ceil(($lineas - 5) / 5) * 8);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Pdf\Calculadores;

use App\Repository\Models\Catalogos\Producto;

final class CalculadorAlturaProducto implements CalculadorAltura
{
    private const int ALTO_ROW_MM = 6;

    public function altura(mixed $item): int
    {
        if (! $item instanceof Producto) {
            return self::ALTO_ROW_MM;
        }

        $totalFilas = 1 + $item->variantes->count();

        return self::ALTO_ROW_MM * $totalFilas;
    }
}

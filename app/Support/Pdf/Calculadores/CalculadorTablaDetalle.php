<?php

declare(strict_types=1);

namespace App\Support\Pdf\Calculadores;

class CalculadorTablaDetalle implements CalculadorAltura
{
    private const int ALTO_BASE_MM = 7;

    private const int ALTO_POR_LINEA_MM = 4;

    private const int CHARS_POR_LINEA = 40;

    public function altura(mixed $item): int
    {
        $descripcion = '';

        if (is_object($item) && method_exists($item, 'getAttribute')) {
            $descripcion = (string) ($item->getAttribute('descripcion')
                ?? $item->getAttribute('observaciones')
                ?? $item->getAttribute('notas')
                ?? '');
        } elseif (is_array($item)) {
            $raw = $item['descripcion'] ?? $item['observaciones'] ?? $item['notas'] ?? '';
            $descripcion = is_string($raw) ? $raw : '';
        }

        $len = mb_strlen($descripcion);
        if ($len <= self::CHARS_POR_LINEA) {
            return self::ALTO_BASE_MM;
        }

        $lineasExtra = (int) ceil($len / self::CHARS_POR_LINEA) - 1;

        return self::ALTO_BASE_MM + $lineasExtra * self::ALTO_POR_LINEA_MM;
    }
}

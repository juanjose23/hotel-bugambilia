<?php

declare(strict_types=1);

namespace App\Support\Pdf\Calculadores;

class CalculadorTablaDetalle implements CalculadorAltura
{
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
        if ($len <= 80) {
            return 6;
        }

        return 6 + (int) (ceil($len / 80) * 3);
    }
}

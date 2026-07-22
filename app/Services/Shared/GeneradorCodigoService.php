<?php

declare(strict_types=1);

namespace App\Services\Shared;

class GeneradorCodigoService
{
    /**
     * Genera un código correlativo secuencial con un prefijo para un modelo dado.
     */
    public function generarCorrelativo(
        string $prefix,
        string $modelClass,
        string $column = 'codigo',
        int $padLength = 4
    ): string {
        $prefixUpper = strtoupper($prefix).'-';

        $ultimo = $modelClass::withTrashed()
            ->where($column, 'like', $prefixUpper.'%')
            ->latest('id')
            ->first();

        $numero = 1;
        if ($ultimo) {
            $val = $ultimo->getAttribute($column);
            if (is_string($val) && preg_match('/^'.preg_quote($prefixUpper, '/').'(\d+)$/', $val, $matches)) {
                $numero = intval($matches[1]) + 1;
            } else {
                $maxId = $modelClass::withTrashed()->max('id');
                $numero = (is_numeric($maxId) ? (int) $maxId : 0) + 1;
            }
        } else {
            $maxId = $modelClass::withTrashed()->max('id');
            $numero = (is_numeric($maxId) ? (int) $maxId : 0) + 1;
        }

        return $prefixUpper.str_pad((string) $numero, $padLength, '0', STR_PAD_LEFT);
    }

    /**
     * Genera un código de barras limpio basado en el nombre y código de variante.
     */
    public function generarCodigoBarras(string $nombre, ?string $varianteCodigo = null): string
    {
        $codigo = $varianteCodigo ?? $nombre;

        return trim(str_replace([' ', '-', '/'], '', $codigo));
    }
}

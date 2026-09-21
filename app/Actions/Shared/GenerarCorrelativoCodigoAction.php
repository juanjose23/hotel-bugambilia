<?php

declare(strict_types=1);

namespace App\Actions\Shared;

use Illuminate\Database\Eloquent\Model;

final class GenerarCorrelativoCodigoAction
{
    /**
     * Genera un código correlativo secuencial con un prefijo para un modelo dado.
     *
     * @param  class-string  $modelClass
     */
    public function ejecutar(
        string $prefix,
        string $modelClass,
        string $column = 'codigo',
        int $padLength = 4
    ): string {
        $prefixUpper = strtoupper($prefix).'-';

        /** @var Model|null $ultimo */
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
}

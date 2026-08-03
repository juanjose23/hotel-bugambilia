<?php

declare(strict_types=1);

namespace App\Actions\Restaurante;

final class NormalizarMetaDatosAction
{
    /**
     * Normaliza meta_datos que pueden llegar como array, JSON string o null.
     *
     * @return array<mixed, mixed>
     */
    public function ejecutar(mixed $metaDatos): array
    {
        if (is_array($metaDatos)) {
            return $metaDatos;
        }

        if (is_string($metaDatos) && $metaDatos !== '') {
            $decoded = json_decode($metaDatos, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}

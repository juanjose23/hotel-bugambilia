<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Repository\Models\Catalogos\Catalogo;

final class GeneradorPrefijo
{
    public function generarDesdeCategoria(Catalogo $categoria): string
    {

        if (filled($categoria->prefijo)) {
            return strtoupper(trim($categoria->prefijo));
        }

        return $this->prefijoDesdNombre($categoria->nombre);
    }

    public function prefijoDesdNombre(string $nombre): string
    {
        $limpio = preg_replace('/[^a-záéíóúñA-ZÁÉÍÓÚÑ]/u', '', $this->normalizar($nombre));

        return strtoupper(substr($limpio ?? '', 0, 3)) ?: 'ACT';
    }

    private function normalizar(string $texto): string
    {
        $mapa = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
        ];

        return strtolower(strtr($texto, $mapa));
    }
}

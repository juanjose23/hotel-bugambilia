<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;

final class GeneradorPrefijo
{
    public function generarDesdeProducto(?Producto $producto): string
    {
        if (! $producto) {
            return 'ACT';
        }

        if ($producto->categoria) {
            return $this->generarDesdeCategoria($producto->categoria);
        }

        return $this->prefijoDesdeNombre($producto->nombre);
    }

    public function generarDesdeCategoria(?Catalogo $categoria): string
    {
        if (! $categoria) {
            return 'ACT';
        }

        if (filled($categoria->prefijo)) {
            return strtoupper(trim((string) $categoria->prefijo));
        }

        return $this->prefijoDesdeNombre($categoria->nombre);
    }

    public function prefijoDesdeNombre(?string $nombre): string
    {
        if (blank($nombre)) {
            return 'ACT';
        }

        $limpio = preg_replace('/[^a-záéíóúñA-ZÁÉÍÓÚÑ]/u', '', $this->normalizar($nombre));

        return strtoupper(substr($limpio ?? '', 0, 3)) ?: 'ACT';
    }

    public function prefijoDesdNombre(string $nombre): string
    {
        return $this->prefijoDesdeNombre($nombre);
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

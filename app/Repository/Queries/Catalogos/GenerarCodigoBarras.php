<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;

final class GenerarCodigoBarras
{
    public function ejecutar(Producto $producto, ?ProductoVariante $variante = null): string
    {
        $codigo = $variante !== null ? $variante->codigo : (string) $producto->nombre;

        return trim(str_replace([' ', '-', '/'], '', $codigo));
    }

    /** @return list<string> */
    public function generarLote(Producto $producto): array
    {
        $codigosGenerados = [];
        $variantes = $producto->variantes;
        if ($variantes->isNotEmpty()) {
            foreach ($variantes as $variante) {
                $codigosGenerados[] = $this->ejecutar($producto, $variante);
            }
        } else {
            $codigosGenerados[] = $this->ejecutar($producto);
        }

        return $codigosGenerados;
    }
}

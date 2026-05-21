<?php

namespace App\UseCases\Catalogos\Queries;

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;

class GenerarCodigoBarrasUseCase
{
    public function ejecutar(Producto $producto, ?ProductoVariante $variante = null): string
    {
        $codigo = $variante->codigo ?? $producto->nombre;
        $codigoBarras = trim(str_replace([' ', '-', '/'], '', $codigo));

        return $codigoBarras;
    }

    /** @return array<int, string> */
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

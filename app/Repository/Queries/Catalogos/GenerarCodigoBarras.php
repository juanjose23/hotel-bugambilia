<?php

declare(strict_types=1);

namespace App\Repository\Queries\Catalogos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Services\Shared\GeneradorCodigoService;

class GenerarCodigoBarras
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo,
    ) {}

    public function ejecutar(Producto $producto, ?ProductoVariante $variante = null): string
    {
        return $this->generadorCodigo->generarCodigoBarras((string) $producto->nombre, $variante?->codigo);
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

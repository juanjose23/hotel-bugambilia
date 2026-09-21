<?php

declare(strict_types=1);

namespace App\BusinessLogic\Activos;

use App\Repository\Models\Catalogos\Producto;
use App\Repository\Persistencia\Activos\PrefijoCodigoRepositorioInterface;

final readonly class GeneradorCodigoInventario
{
    public function __construct(
        private PrefijoCodigoRepositorioInterface $prefijoCodigoRepositorio,
        private GeneradorPrefijo $generadorPrefijo,
    ) {}

    public function generar(Producto $producto): string
    {
        $prefijoStr = $this->generadorPrefijo->generarDesdeProducto($producto);

        return $this->prefijoCodigoRepositorio->generarSiguienteCodigo($prefijoStr);
    }
}

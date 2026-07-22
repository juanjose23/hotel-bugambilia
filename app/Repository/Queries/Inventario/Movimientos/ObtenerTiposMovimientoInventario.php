<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Movimientos;

use App\Repository\Models\Catalogos\Catalogo;

class ObtenerTiposMovimientoInventario
{
    public function __construct(
        private readonly Catalogo $catalogo,
    ) {}

    /** @return array<string, string> */
    public function execute(): array
    {
        return $this->loadTiposMovimiento();
    }

    /** @return array<string, string> */
    private function loadTiposMovimiento(): array
    {
        /** @var array<string, string> $result */
        $result = $this->catalogo->query()
            ->whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'TIPO_MOVIMIENTO_INV'))
            ->pluck('nombre', 'codigo')
            ->toArray();

        return $result;
    }
}

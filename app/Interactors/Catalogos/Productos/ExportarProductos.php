<?php

declare(strict_types=1);

namespace App\Interactors\Catalogos\Productos;

use App\Repository\Queries\Catalogos\ExportProductos;

final readonly class ExportarProductos
{
    public function __construct(
        private ExportProductos $query,
    ) {}

    /** @param array<string, mixed> $filtros */
    public function ejecutar(array $filtros = []): string
    {
        return $this->query->exportarCsv($filtros);
    }
}

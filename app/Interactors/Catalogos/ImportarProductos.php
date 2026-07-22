<?php

declare(strict_types=1);

namespace App\Interactors\Catalogos;

use App\BusinessLogic\Catalogos\ImportadorProductos;

class ImportarProductos
{
    public function __construct(
        private readonly ImportadorProductos $importador,
    ) {}

    /** @return array{processed: int, errors: array<int, string>} */
    public function ejecutar(string $path): array
    {
        return $this->importador->importarDesdeCsv($path);
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final readonly class ConfiguracionPagina
{
    public int $totalPorPagina;

    public function __construct(
        public int $filas = 28,
        public ?int $columnas = null,
        public int $altoFilaMm = 4,
        public int $altoEncabezadoMm = 4,
        public int $altoPieMm = 15,
    ) {
        $this->totalPorPagina = $this->columnas
            ? $this->filas * $this->columnas
            : $this->filas;
    }
}

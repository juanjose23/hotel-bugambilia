<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final readonly class ConfiguracionPagina
{
    public int $totalPorPagina;

    public function __construct(
        public int $filas = 30,
        public ?int $columnas = null,
        public int $altoFilaMm = 7,
        public int $altoEncabezadoMm = 5,
        public int $altoPieMm = 15,
    ) {
        $this->totalPorPagina = $this->columnas
            ? $this->filas * $this->columnas
            : $this->filas;
    }
}

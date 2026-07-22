<?php

declare(strict_types=1);

namespace App\BusinessLogic\Limpieza\Data;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;

final readonly class TerminarLimpiezaData
{
    /**
     * @param  array<int|string, bool>  $checklist
     * @param  array<int|string, float>  $consumos
     */
    public function __construct(
        public LimpiezaEjecucion $record,
        public array $checklist,
        public string $observaciones,
        public array $consumos,
    ) {}
}

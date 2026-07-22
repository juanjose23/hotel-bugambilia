<?php

declare(strict_types=1);

namespace App\Events\Inventario;

use App\Repository\Models\Inventario\Lote;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class MermaRegistrada
{
    use Dispatchable;

    public function __construct(
        public Lote $lote,
        public float $cantidad,
        public string $motivo,
        public int $registradoPorId,
    ) {}
}

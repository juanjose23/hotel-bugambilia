<?php

declare(strict_types=1);

namespace App\Events\Limpieza;

use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class FaltanteReposicionDetectado
{
    use Dispatchable;

    /**
     * @param  array<int, array{variante_id: int|null, nombre: string, required: float, available: float}>  $items
     */
    public function __construct(
        public LimpiezaEjecucion $ejecucion,
        public array $items,
        public User $destinatario,
    ) {}
}

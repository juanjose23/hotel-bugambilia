<?php

declare(strict_types=1);

namespace App\Events\Restaurante;

use App\Repository\Models\Restaurante\Pedido;
use Illuminate\Foundation\Events\Dispatchable;

final class PedidoEnviadoACocina
{
    use Dispatchable;

    public function __construct(
        public readonly Pedido $pedido,
        /** @var list<int> IDs de items recién procesados */
        public readonly array $itemIds,
    ) {}
}

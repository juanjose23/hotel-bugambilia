<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Actions\Restaurante\Cocina\ReimprimirComandaAction;
use App\Repository\Models\Restaurante\Pedido;

final class ReimprimirComanda
{
    public function __construct(
        private readonly ReimprimirComandaAction $action,
    ) {}

    public function ejecutar(int $pedidoId, ?string $area = null, ?int $userId = null, ?string $ipAddress = null): Pedido
    {
        return $this->action->ejecutar($pedidoId, $area, $userId, $ipAddress);
    }
}

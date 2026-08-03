<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Cocina;

use App\Actions\Restaurante\Cocina\ReimprimirComandaAction;
use App\Repository\Models\Restaurante\Pedido;

final readonly class ReimprimirComanda
{
    public function __construct(
        private ReimprimirComandaAction $action,
    ) {}

    public function ejecutar(int $pedidoId): Pedido
    {
        return $this->action->ejecutar($pedidoId);
    }
}

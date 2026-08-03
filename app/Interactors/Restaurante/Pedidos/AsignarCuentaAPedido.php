<?php

declare(strict_types=1);

namespace App\Interactors\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;

final class AsignarCuentaAPedido
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(Pedido $pedido, int $cuentaId): Pedido
    {
        if ($pedido->cuenta_id === $cuentaId) {
            return $pedido;
        }

        $pedido->cuenta_id = $cuentaId;
        $this->repositorio->guardarPedido($pedido);

        return $pedido;
    }
}

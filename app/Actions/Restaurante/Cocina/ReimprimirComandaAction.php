<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Cocina;

use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use DomainException;

final class ReimprimirComandaAction
{
    public function __construct(
        private readonly RestauranteRepositorioInterface $repositorio,
    ) {}

    public function ejecutar(int $pedidoId): Pedido
    {
        $pedido = $this->repositorio->obtenerPedidoPorId($pedidoId);

        if (! $pedido instanceof Pedido) {
            throw new DomainException("Pedido #{$pedidoId} no encontrado para reimpresión.");
        }

        $pedido->increment('consecutivo_comanda');

        return $pedido->refresh();
    }
}

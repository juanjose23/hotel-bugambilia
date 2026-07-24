<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;

interface RestauranteRepositorioInterface
{
    public function guardarItem(PedidoItem $item): void;

    public function guardarPedido(Pedido $pedido): void;

    public function guardarMesa(Espacio $mesa): void;

    public function guardarStock(Stock $stock): void;

    /** @param array<string, mixed> $datos */
    public function registrarMovimiento(array $datos): void;
}

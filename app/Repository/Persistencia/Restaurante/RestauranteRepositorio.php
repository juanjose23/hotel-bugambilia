<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Restaurante;

use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Inventario\MovimientoStock;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use App\Repository\Models\Shared\Stock;

final class RestauranteRepositorio implements RestauranteRepositorioInterface
{
    public function guardarItem(PedidoItem $item): void
    {
        $item->save();
    }

    public function guardarPedido(Pedido $pedido): void
    {
        $pedido->save();
    }

    public function guardarMesa(Espacio $mesa): void
    {
        $mesa->save();
    }

    public function guardarStock(Stock $stock): void
    {
        $stock->save();
    }

    public function registrarMovimiento(array $datos): void
    {
        MovimientoStock::query()->create($datos);
    }
}

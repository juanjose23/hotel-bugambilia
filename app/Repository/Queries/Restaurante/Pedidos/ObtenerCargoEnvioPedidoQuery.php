<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Cuentas\CuentaDetalle;
use App\Repository\Models\Restaurante\Pedido;

final readonly class ObtenerCargoEnvioPedidoQuery
{
    public function ejecutar(Pedido $pedido): ?CuentaDetalle
    {
        return CuentaDetalle::query()->where('cuenta_id', $pedido->cuenta_id)
            ->where('estado', EstadoGeneral::Activo->value)
            ->where('metadatos->pedido_delivery_id', $pedido->id)->first();
    }
}

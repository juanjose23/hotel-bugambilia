<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Support\MonedaHelper;

final class GenerarVoucherPedidoHTML
{
    public function ejecutar(Pedido $pedido): string
    {
        $pedido->loadMissing(['items.plato', 'mesa', 'mesero.persona', 'cliente.persona', 'cuenta.estancia.habitacion', 'cuenta.moneda']);

        $items = $pedido->items->filter(fn ($item) => $item->estado !== EstadoItemPedido::ANULADO);

        $total = (float) $pedido->subtotal;

        $clienteNombre = $pedido->cliente->nombre_completo ?? ('Cliente '.($pedido->mesa->nombre ?? 'Mostrador'));
        $meseroNombre = $pedido->mesero->persona->nombre_completo ?? null;
        $habitacionNumero = $pedido->cuenta->estancia->habitacion->numero ?? null;
        $simboloMoneda = MonedaHelper::simbolo($pedido->cuenta?->moneda);

        return view('reports.restaurante.voucher-pedido-pos', [
            'pedido' => $pedido,
            'items' => $items,
            'clienteNombre' => $clienteNombre,
            'meseroNombre' => $meseroNombre,
            'habitacionNumero' => $habitacionNumero,
            'simboloMoneda' => $simboloMoneda,
            'total' => $total,
        ])->render();
    }
}

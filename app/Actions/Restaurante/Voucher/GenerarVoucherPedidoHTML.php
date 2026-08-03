<?php

declare(strict_types=1);

namespace App\Actions\Restaurante\Voucher;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Repository\Models\Restaurante\Pedido;

final class GenerarVoucherPedidoHTML
{
    public function ejecutar(Pedido $pedido): string
    {
        $pedido->loadMissing(['items.plato', 'mesa', 'mesero.persona', 'cliente.personaNatural', 'cliente.personaJuridica', 'cuenta.estancia.habitacion', 'cuenta.moneda']);

        $items = $pedido->items->filter(fn ($item) => $item->estado !== EstadoItemPedido::ANULADO);

        $total = (float) $pedido->subtotal;

        $clienteNombre = $pedido->cliente->nombre_completo ?? ('Cliente '.($pedido->mesa->nombre ?? 'Mostrador'));
        $meseroNombre = $pedido->mesero->persona->nombre_completo ?? null;
        $habitacionNumero = $pedido->cuenta->estancia->habitacion->numero ?? null;
        $simboloMoneda = (string) ($pedido->cuenta->moneda->simbolo ?? 'C$');

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

<?php

declare(strict_types=1);

namespace App\Presenters\Restaurante;

use App\Enums\Facturacion\EstadoTransaccionPago;
use App\Repository\Models\Facturacion\PagoTransaccion;
use App\Repository\Models\Restaurante\Pedido;

final readonly class SeguimientoPedidoPresenter
{
    /** @return array<string, mixed> */
    public function presentar(Pedido $registro, ?PagoTransaccion $transaccion): array
    {
        $stripe = $transaccion !== null && in_array($transaccion->estado, [EstadoTransaccionPago::Pendiente, EstadoTransaccionPago::Fallida], true)
            && is_string($transaccion->response_payload['client_secret'] ?? null)
            ? ['client_secret' => $transaccion->response_payload['client_secret'], 'publishable_key' => config('services.stripe.key'),
                'monto' => (float) $transaccion->monto, 'moneda' => $registro->cuenta?->moneda->simbolo ?? 'C$'] : null;

        return [
            'id' => $registro->id, 'codigo' => $registro->codigo,
            'estado' => $registro->estado->value, 'estado_nombre' => $registro->estado->getLabel(),
            'total' => (float) ($registro->cuenta->total ?? $registro->subtotal),
            'pagado' => (float) ($registro->cuenta->total_pagado ?? 0),
            'saldo' => (float) ($registro->cuenta->saldo ?? $registro->subtotal),
            'moneda' => $registro->cuenta?->moneda->simbolo ?? 'C$',
            'items' => $registro->items->map(fn ($item): array => ['id' => $item->id,
                'nombre' => $item->plato->nombre ?? 'Producto', 'cantidad' => (float) $item->cantidad,
                'subtotal' => (float) $item->subtotal, 'estado' => $item->estado->getLabel()])->values()->all(),
            'stripe' => $stripe,
        ];
    }
}

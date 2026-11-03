<?php

declare(strict_types=1);

namespace App\Http\Requests\Restaurante;

use App\Repository\Queries\Restaurante\Pedidos\AutorizarPedidoPublicoQuery;
use Illuminate\Foundation\Http\FormRequest;

final class ConfirmarPagoStripePedidoRequest extends FormRequest
{
    public function authorize(AutorizarPedidoPublicoQuery $autorizacion): bool
    {
        return $autorizacion->ejecutar($this, $this->integer('pedido_id'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'pedido_id' => ['required', 'integer', 'exists:pedidos,id'],
            'payment_intent_id' => ['required', 'string'],
        ];
    }
}

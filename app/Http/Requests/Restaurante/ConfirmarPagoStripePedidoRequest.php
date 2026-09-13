<?php

declare(strict_types=1);

namespace App\Http\Requests\Restaurante;

use Illuminate\Foundation\Http\FormRequest;

final class ConfirmarPagoStripePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

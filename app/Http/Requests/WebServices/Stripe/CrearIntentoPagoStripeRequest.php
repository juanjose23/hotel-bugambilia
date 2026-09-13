<?php

declare(strict_types=1);

namespace App\Http\Requests\WebServices\Stripe;

use Illuminate\Foundation\Http\FormRequest;

final class CrearIntentoPagoStripeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reserva_id' => ['required', 'integer', 'exists:reservas,id'],
            'codigo_reserva' => ['required', 'string', 'max:80'],
        ];
    }
}

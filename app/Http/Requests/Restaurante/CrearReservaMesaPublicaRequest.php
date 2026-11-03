<?php

declare(strict_types=1);

namespace App\Http\Requests\Restaurante;

use Illuminate\Foundation\Http\FormRequest;

final class CrearReservaMesaPublicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_cliente' => ['required', 'string', 'min:3', 'max:100'],
            'telefono_cliente' => ['required', 'string', 'min:8', 'max:25'],
            'email_cliente' => ['nullable', 'email', 'max:100'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora' => ['required', 'date_format:H:i'],
            'duracion_horas' => ['nullable', 'integer', 'min:1', 'max:5'],
            'adultos' => ['required', 'integer', 'min:1', 'max:30'],
            'espacio_id' => ['nullable', 'integer', 'exists:espacios,id'],
            'tipo_pago_reserva' => ['nullable', 'string', 'in:sin_pago,abono_50,pago_total'],
            'canal_pago_reserva' => ['nullable', 'string', 'in:stripe,efectivo'],
            'promocion_id' => ['nullable', 'integer', 'exists:promociones,id'],
            'items_preorden' => ['nullable', 'array', 'max:50'],
            'items_preorden.*.plato_id' => ['required', 'integer', 'distinct', 'exists:platos,id'],
            'items_preorden.*.cantidad' => ['required', 'integer', 'min:1', 'max:50'],
            'items_preorden.*.observaciones' => ['nullable', 'string', 'max:150'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}

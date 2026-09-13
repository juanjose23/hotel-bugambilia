<?php

declare(strict_types=1);

namespace App\Http\Requests\Restaurante;

use Illuminate\Foundation\Http\FormRequest;

final class CrearPedidoPublicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'telefono' => ['required', 'string', 'min:8', 'max:25'],
            'departamento' => ['nullable', 'string', 'max:100'],
            'municipio' => ['nullable', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'min:5', 'max:255'],
            'metodo_pago' => ['required', 'string', 'in:efectivo,stripe'],
            'monto_paga_con' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.plato_id' => ['required', 'integer', 'exists:platos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.observaciones' => ['nullable', 'string', 'max:150'],
        ];
    }
}

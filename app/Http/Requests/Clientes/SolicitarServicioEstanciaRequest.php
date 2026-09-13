<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

final class SolicitarServicioEstanciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'servicio_id' => ['required', 'integer', 'exists:servicios,id'],
            'cantidad' => ['required', 'numeric', 'min:1', 'max:50'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array{servicio_id: int, cantidad: float, notas?: string|null} */
    public function datosServicio(): array
    {
        /** @var array{servicio_id: int, cantidad: float, notas?: string|null} $validated */
        $validated = $this->validated();

        return [
            'servicio_id' => $validated['servicio_id'],
            'cantidad' => $validated['cantidad'],
            'notas' => $validated['notas'] ?? null,
        ];
    }
}

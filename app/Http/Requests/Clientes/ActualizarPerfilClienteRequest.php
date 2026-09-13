<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use App\Repository\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ActualizarPerfilClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->user() instanceof User ? (int) $this->user()->id : 0;

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:users,email,{$userId}"],
            'telefono' => ['nullable', 'string', 'max:30'],
            'identificacion' => ['nullable', 'string', 'max:50'],
            'tipo_identificacion' => ['nullable', 'string', 'max:50'],
        ];
    }
}

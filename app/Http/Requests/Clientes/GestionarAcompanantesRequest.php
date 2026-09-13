<?php

declare(strict_types=1);

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

final class GestionarAcompanantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'acompanantes' => ['required', 'array'],
            'acompanantes.*.nombre' => ['required', 'string', 'max:150'],
            'acompanantes.*.identificacion' => ['nullable', 'string', 'max:50'],
            'acompanantes.*.tipo' => ['nullable', 'string', 'in:adulto,nino,bebe'],
        ];
    }

    /** @return array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null}> */
    public function acompanantes(): array
    {
        /** @var array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null}> $acompanantes */
        $acompanantes = $this->validated('acompanantes');

        return $acompanantes;
    }
}

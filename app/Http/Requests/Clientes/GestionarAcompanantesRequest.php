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
            'acompanantes' => ['present', 'array', 'max:50'],
            'acompanantes.*.huesped_id' => ['nullable', 'integer', 'min:1', 'distinct'],
            'acompanantes.*.reserva_detalle_id' => ['nullable', 'integer', 'min:1'],
            'acompanantes.*.nombre' => ['required', 'string', 'max:150'],
            'acompanantes.*.identificacion' => ['nullable', 'string', 'max:50'],
            'acompanantes.*.tipo' => ['nullable', 'string', 'in:adulto,nino,bebe'],
        ];
    }

    /** @return array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null, huesped_id?: int|null, reserva_detalle_id?: int|null}> */
    public function acompanantes(): array
    {
        /** @var array<int, array{nombre: string, identificacion?: string|null, tipo?: string|null, huesped_id?: int|null, reserva_detalle_id?: int|null}> $acompanantes */
        $acompanantes = $this->validated('acompanantes');

        return $acompanantes;
    }
}

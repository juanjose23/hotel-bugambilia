<?php

declare(strict_types=1);

namespace App\Http\Requests\WebServices\Reservas;

use Illuminate\Foundation\Http\FormRequest;

final class CancelarReservaWebServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'motivo' => ['nullable', 'string', 'max:255'],
        ];
    }
}

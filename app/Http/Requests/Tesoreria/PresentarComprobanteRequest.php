<?php

declare(strict_types=1);

namespace App\Http\Requests\Tesoreria;

use Illuminate\Foundation\Http\FormRequest;

final class PresentarComprobanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && config('tesoreria.habilitado') === true;
    }

    /** @return array<string,list<string>> */
    public function rules(): array
    {
        return ['banco_id' => ['required', 'integer', 'min:1'], 'importe' => ['required', 'numeric', 'gt:0', 'decimal:0,2'], 'fecha' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'], 'referencia' => ['required', 'string', 'min:3', 'max:150'], 'archivo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'], 'clave' => ['required', 'uuid'], 'aclaracion_id' => ['nullable', 'integer', 'min:1']];
    }
}

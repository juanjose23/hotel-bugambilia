<?php

declare(strict_types=1);

namespace App\Http\Requests\Habitaciones;

use Illuminate\Foundation\Http\FormRequest;

final class ConsultarDisponibilidadHabitacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fecha_check_in' => ['required', 'date'],
            'fecha_check_out' => ['required', 'date', 'after:fecha_check_in'],
            'adultos' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ninos' => ['nullable', 'integer', 'min:0', 'max:20'],
        ];
    }
}

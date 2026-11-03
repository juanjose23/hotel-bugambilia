<?php

declare(strict_types=1);

namespace App\Http\Requests\Promociones;

use Illuminate\Foundation\Http\FormRequest;

final class ReservarPromocionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'opcion' => ['required', 'string', 'max:80'],
            'nombre_cliente' => ['required', 'string', 'min:3', 'max:150'],
            'telefono_cliente' => ['required', 'string', 'min:8', 'max:25'],
            'email_cliente' => ['required', 'email', 'max:150'],
            'fecha_check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'fecha_check_out' => ['nullable', 'date_format:Y-m-d', 'after:fecha_check_in'],
            'hora_reserva' => ['required', 'date_format:H:i'],
            'adultos' => ['required', 'integer', 'min:1', 'max:30'],
            'duracion_horas' => ['required', 'integer', 'min:1', 'max:5'],
            'tipo_pago_reserva' => ['required', 'in:sin_pago,abono_50,pago_total'],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }
}

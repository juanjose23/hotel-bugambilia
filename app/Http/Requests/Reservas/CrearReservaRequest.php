<?php

declare(strict_types=1);

namespace App\Http\Requests\Reservas;

use App\Enums\Reservas\TipoReserva;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CrearReservaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nombre_cliente' => ['required', 'string', 'max:150'],
            'telefono_cliente' => ['nullable', 'string', 'max:50'],
            'email_cliente' => ['nullable', 'email', 'max:150'],
            'tipo_reserva' => ['required', Rule::enum(TipoReserva::class)],
            'habitacion_id' => ['nullable', 'required_if:tipo_reserva,habitacion', 'integer', 'exists:habitaciones,id'],
            'espacio_id' => ['nullable', 'required_if:tipo_reserva,restaurante', 'integer', 'exists:espacios,id'],
            'servicio_id' => ['nullable', 'required_if:tipo_reserva,servicio', 'integer', 'exists:servicios,id'],
            'promocion_id' => ['nullable', 'integer', 'exists:promociones,id'],
            'fecha_check_in' => ['required', 'date', 'after_or_equal:today'],
            'fecha_check_out' => ['nullable', 'required_if:tipo_reserva,habitacion', 'date', 'after:fecha_check_in'],
            'hora_reserva' => ['nullable', 'date_format:H:i'],
            'hora_fin' => ['nullable', 'date_format:H:i'],
            'adultos' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ninos' => ['nullable', 'integer', 'min:0', 'max:20'],
            'solicita_cuenta' => ['nullable', 'boolean'],
            'limite_cuenta_solicitado' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'notas' => ['nullable', 'string', 'max:2000'],
            'acompanantes' => ['nullable', 'array', 'max:20'],
            'acompanantes.*.nombre' => ['required_with:acompanantes', 'string', 'max:150'],
            'acompanantes.*.identificacion' => ['nullable', 'string', 'max:100'],
            'acompanantes.*.tipo' => ['nullable', 'string', 'max:50'],
            'servicios_adicionales' => ['nullable', 'array', 'max:20'],
            'servicios_adicionales.*.servicio_id' => ['required', 'integer', 'distinct', 'exists:servicios,id'],
            'servicios_adicionales.*.cantidad' => ['nullable', 'integer', 'min:1', 'max:100'],
            'espacios_adicionales' => ['nullable', 'array', 'max:20'],
            'espacios_adicionales.*.espacio_id' => ['required', 'integer', 'distinct', 'exists:espacios,id'],
            'espacios_adicionales.*.cantidad' => ['nullable', 'integer', 'min:1', 'max:1'],
        ];
    }
}

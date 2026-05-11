<?php

namespace App\Http\Requests\Personas;

use App\Rules\Personas\PersonaNatural\ValidCedulaNicaragua;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePersonaNaturalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'primer_nombre' => ['required', 'string', 'max:100'],
            'segundo_nombre' => ['nullable', 'string', 'max:100'],
            'pais_id' => ['nullable', 'exists:paises,id'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'primer_apellido' => ['required', 'string', 'max:100'],
            'segundo_apellido' => ['nullable', 'string', 'max:100'],
            'tipo_identificacion' => ['nullable', Rule::in(['cedula', 'dni', 'pasaporte', 'residencia', 'nit', 'ruc', 'otro'])],
            'numero_identificacion' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('personas_naturales')->where(fn ($q) => $q->where('tipo_identificacion', $this->tipo_identificacion)),
            ],
            'sexo' => ['nullable', Rule::in(['M', 'F', 'O'])],
            'fecha_nacimiento' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->sometimes('numero_identificacion', ['required', new ValidCedulaNicaragua], function ($input) {
            return $input->tipo_identificacion === 'cedula';
        });

        $validator->after(function ($validator) {
            $data = $this->validationData();
            if ($data['tipo_identificacion'] && ! $data['numero_identificacion']) {
                $validator->errors()->add('numero_identificacion', 'El número de identificación es requerido cuando se especifica el tipo.');
            }
            if (! $data['tipo_identificacion'] && $data['numero_identificacion']) {
                $validator->errors()->add('tipo_identificacion', 'El tipo de identificación es requerido cuando se especifica el número.');
            }
        });
    }
}

<?php

namespace App\Http\Requests\Colaboradores;

use App\Rules\Personas\PersonaNatural\ValidCedulaNicaragua;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateColaboradorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'persona.primer_nombre' => ['sometimes', 'string', 'max:100'],
            'persona.segundo_nombre' => ['nullable', 'string', 'max:100'],
            'persona.pais_id' => ['nullable', 'exists:paises,id'],
            'persona.telefono' => ['nullable', 'string', 'max:20'],
            'persona.direccion' => ['nullable', 'string', 'max:255'],
            'persona.personaNatural.primer_apellido' => ['sometimes', 'string', 'max:100'],
            'persona.personaNatural.segundo_apellido' => ['nullable', 'string', 'max:100'],
            'persona.personaNatural.tipo_identificacion' => ['nullable', Rule::in(['cedula', 'dni', 'pasaporte', 'residencia', 'nit', 'ruc', 'otro'])],
            'persona.personaNatural.numero_identificacion' => [
                'nullable', 
                'string', 
                'max:30',
                Rule::unique('personas_naturales', 'numero_identificacion')
                    ->where(fn ($q) => $q->where('tipo_identificacion', $this->input('persona.personaNatural.tipo_identificacion'))->whereNull('deleted_at'))
                    ->ignore($this->route('persona_natural'))
            ],
            'persona.personaNatural.sexo' => ['nullable', Rule::in(['M', 'F', 'O'])],
            'persona.personaNatural.fecha_nacimiento' => ['nullable', 'date'],
            'codigo' => [
                'nullable', 
                'string', 
                'max:30', 
                Rule::unique('colaboradores')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('colaborador'))
            ],
            'nss' => ['nullable', 'string', 'max:30'],
            'fecha_ingreso' => ['nullable', 'date'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->sometimes('numero_identificacion', ['required', new ValidCedulaNicaragua()], function ($input) {
            return $input->tipo_identificacion === 'cedula';
        });

        $validator->after(function ($validator) {
            $data = $this->validationData();
            if ($data['tipo_identificacion'] && !$data['numero_identificacion']) {
                $validator->errors()->add('numero_identificacion', 'El número de identificación es requerido cuando se especifica el tipo.');
            }
            if (!$data['tipo_identificacion'] && $data['numero_identificacion']) {
                $validator->errors()->add('tipo_identificacion', 'El tipo de identificación es requerido cuando se especifica el número.');
            }
        });
    }
}

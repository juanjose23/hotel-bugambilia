<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Repository\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tipoPersona = $this->input('tipo_persona', 'natural');

        $base = [
            'tipo_persona' => ['required', 'string', 'in:natural,juridica'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::where('email', $value)->first();
                    if ($user instanceof User && $user->persona && $user->persona->cliente()->exists()) {
                        $fail('Este correo electrónico ya está registrado con una cuenta existente. Por favor inicie sesión.');
                    }
                },
            ],
            'phone' => [$tipoPersona === 'juridica' ? 'required' : 'nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'tipo_identificacion' => ['nullable', 'string', 'max:30', 'required_with:numero_identificacion'],
            'numero_identificacion' => ['nullable', 'string', 'max:50', 'required_with:tipo_identificacion'],
        ];

        if ($tipoPersona === 'juridica') {
            $base['razon_social'] = ['required', 'string', 'max:100'];
            $base['primer_nombre'] = ['nullable', 'string', 'max:100'];
        } else {
            $base['primer_nombre'] = ['required', 'string', 'max:100'];
            $base['primer_apellido'] = ['required', 'string', 'max:100'];
        }

        return $base;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'phone.required' => 'El teléfono es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'primer_nombre.required' => 'El nombre es obligatorio.',
            'primer_apellido.required' => 'El apellido es obligatorio.',
            'tipo_identificacion.required_with' => 'Debe seleccionar el tipo de identificación.',
            'numero_identificacion.required_with' => 'Debe ingresar el número de identificación.',
        ];
    }
}

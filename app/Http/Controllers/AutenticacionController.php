<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\YaTieneCuentaException;
use App\Http\Requests\RegistroRequest;
use App\Interactors\Usuarios\RegistrarCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class AutenticacionController extends Controller
{
    public function iniciarSesion(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $remember = (bool) $request->input('remember', false);

        if (Auth::attempt($credenciales, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended()->with('success', '¡Bienvenido de vuelta!');
        }

        throw ValidationException::withMessages([
            'email' => ['Las credenciales ingresadas no coinciden con nuestros registros.'],
        ]);
    }

    public function registrar(RegistroRequest $request, RegistrarCliente $registrarCliente): RedirectResponse
    {
        $datosValidados = $request->validated();

        /** @var array<string, mixed> $datosValidados */
        $tipoPersona = isset($datosValidados['tipo_persona']) && is_string($datosValidados['tipo_persona'])
            ? $datosValidados['tipo_persona']
            : 'natural';

        $datosCliente = $this->construirDatosCliente(
            $datosValidados,
            $tipoPersona,
        );

        try {
            $resultado = $registrarCliente->ejecutar($datosCliente);

            Auth::login($resultado['user']);
            $request->session()->regenerate();

            return redirect('/')->with('success', '¡Registro completado exitosamente!');
        } catch (YaTieneCuentaException $e) {
            Log::warning('Ya existe una cuenta con ese email: '.$e->getMessage());
            throw ValidationException::withMessages([
                'email' => ['Ya existe una cuenta vinculada a este correo electrónico. Por favor inicie sesión.'],
            ]);
        } catch (\RuntimeException $e) {
            return back()->with('warning', $e->getMessage());
        }
    }

    public function cambiarContrasenaForm(): RedirectResponse|Response
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect('/login');
        }

        if (! $user->password_change_required) {
            return redirect('/');
        }

        return Inertia::render('auth/CambiarContrasena');
    }

    public function cambiarContrasena(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect('/login');
        }

        $validado = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación no coincide.',
        ]);

        if (! Hash::check($validado['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validado['password']),
            'password_change_required' => false,
        ]);

        return redirect('/')->with('success', 'Contraseña actualizada exitosamente.');
    }

    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Sesión cerrada correctamente.');
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function construirDatosCliente(array $datos, string $tipoPersona): array
    {
        $cliente = [
            'tipo_persona' => $tipoPersona,
            'catalogo_id' => 1,
            'primer_nombre' => $datos['primer_nombre'] ?? $datos['razon_social'] ?? '',
            'email' => $datos['email'],
            'telefono' => $datos['phone'] ?? '',
            'password' => $datos['password'],
            'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
            'numero_identificacion' => $datos['numero_identificacion'] ?? null,
        ];

        if ($tipoPersona === 'juridica') {
            $cliente['razon_social'] = $datos['razon_social'] ?? '';
        } else {
            $cliente['primer_apellido'] = $datos['primer_apellido'] ?? '';
        }

        return $cliente;
    }
}

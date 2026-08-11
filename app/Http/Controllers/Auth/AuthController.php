<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\BusinessLogic\Usuarios\ConstruirDatosCliente;
use App\Exceptions\YaTieneCuentaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CambiarContrasenaRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegistroRequest;
use App\Interactors\Usuarios\Clientes\RegistrarCliente;
use App\Interactors\Usuarios\Credenciales\CambiarContrasena;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

final class AuthController extends Controller
{
    public function __construct(
        private readonly RegistrarCliente $registrarCliente,
        private readonly CambiarContrasena $cambiarContrasena,
        private readonly ConstruirDatosCliente $construirDatosCliente,
    ) {}

    public function mostrarLogin(): Response
    {
        return Inertia::render('IniciarSesion');
    }

    public function iniciarSesion(LoginRequest $request): RedirectResponse
    {
        $credenciales = $request->validated();
        $recordar = (bool) $request->boolean('remember');

        if (Auth::attempt($credenciales, $recordar)) {
            $request->session()->regenerate();

            return redirect()->intended()->with('exito', '¡Bienvenido de vuelta!');
        }

        throw ValidationException::withMessages([
            'email' => ['Las credenciales ingresadas no coinciden con nuestros registros.'],
        ]);
    }

    public function mostrarRegistro(): Response
    {
        return Inertia::render('Registro');
    }

    public function registrar(RegistroRequest $request): RedirectResponse
    {
        $datosValidados = $request->validated();
        /** @var array<string, mixed> $datosValidados */
        $tipoPersona = $this->resolverTipoPersona($datosValidados);
        $datosCliente = $this->construirDatosCliente->construir($datosValidados, $tipoPersona);

        try {
            $resultado = $this->registrarCliente->ejecutar($datosCliente);
            Auth::login($resultado['user']);
            $request->session()->regenerate();

            return redirect('/')->with('exito', '¡Registro completado exitosamente!');
        } catch (YaTieneCuentaException $e) {
            Log::warning('Ya existe una cuenta con ese email: '.$e->getMessage());

            throw ValidationException::withMessages([
                'email' => ['Ya existe una cuenta vinculada a este correo electrónico. Por favor inicie sesión.'],
            ]);
        } catch (RuntimeException $e) {
            Log::warning('Ya existe una cuenta con ese email: '.$e->getMessage());

            return back()->with('warning', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Error al registrar cliente: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Ocurrió un error inesperado al procesar el registro.');
        }
    }

    public function mostrarCambiarContrasena(): RedirectResponse|Response
    {
        $usuario = Auth::user();

        if ($usuario === null) {
            return redirect('/login');
        }

        if (! $usuario->password_change_required) {
            return redirect('/');
        }

        return Inertia::render('CambiarContrasena');
    }

    public function cambiarContrasena(CambiarContrasenaRequest $request): RedirectResponse
    {
        $usuario = Auth::user();

        if ($usuario === null) {
            return redirect('/login');
        }

        $validado = $request->validated();

        $currentPassword = is_string($validado['current_password'] ?? null) ? $validado['current_password'] : '';
        $newPassword = is_string($validado['password'] ?? null) ? $validado['password'] : '';

        $this->cambiarContrasena->ejecutar($usuario, $currentPassword, $newPassword);

        return redirect('/')->with('exito', 'Contraseña actualizada exitosamente.');
    }

    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('info', 'Sesión cerrada correctamente.');
    }

    /**
     * Extrae el tipo de persona de los datos validados, con fallback a 'natural'.
     *
     * @param  array<string, mixed>  $datos
     */
    private function resolverTipoPersona(array $datos): string
    {
        return isset($datos['tipo_persona']) && is_string($datos['tipo_persona'])
            ? $datos['tipo_persona']
            : 'natural';
    }
}

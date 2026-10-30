<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interactors\Auth\ProcesarGoogleCallback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly ProcesarGoogleCallback $procesarGoogleCallback,
    ) {}

    public function redireccionar(Request $request): Response
    {
        try {
            /** @var GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $targetUrl = $driver->stateless()->redirect()->getTargetUrl();

            if ($request->header('X-Inertia') !== null) {
                Log::info('[GoogleAuth] Retornando Inertia::location');

                return Inertia::location($targetUrl);
            }

            Log::info('[GoogleAuth] Redirección HTTP directa enviada');

            return redirect()->away($targetUrl);
        } catch (Throwable $e) {
            Log::error('[GoogleAuth] Error generando redirección', ['exception_type' => $e::class]);

            return redirect()->route('login')->with('error', 'No se pudo conectar con Google. Intenta nuevamente más tarde.');
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        Log::info('[GoogleAuth] Callback recibido', [
            'has_code' => $request->has('code'),
            'has_error' => $request->has('error'),
        ]);

        try {
            /** @var GoogleProvider $driver */
            $driver = Socialite::driver('google');

            Log::info('[GoogleAuth] Solicitando datos de usuario a Google...');
            $googleUser = $driver->stateless()->user();

            Log::info('[GoogleAuth] Procesando usuario en base de datos...');
            $usuario = $this->procesarGoogleCallback->ejecutar($googleUser);

            Log::info('[GoogleAuth] Usuario procesado con éxito', [
                'user_id' => $usuario->id,
            ]);

            Auth::login($usuario, true);
            $request->session()->regenerate();

            Log::info('[GoogleAuth] Sesión iniciada exitosamente', [
                'user_id' => $usuario->id,
            ]);

            if ($usuario->esAdmin()) {
                $intended = $request->session()->get('url.intended');
                $request->session()->forget('url.intended');

                $intendedStr = is_string($intended) ? $intended : '';
                $destino = str_contains($intendedStr, '/admin') ? $intendedStr : '/admin';

                return redirect($destino)->with('exito', '¡Has iniciado sesión en el panel administrativo!');
            }

            return redirect()->intended(route('home'))->with('exito', '¡Has iniciado sesión con Google exitosamente!');
        } catch (Throwable $e) {
            Log::error('[GoogleAuth] Error en callback', ['exception_type' => $e::class]);

            return redirect()->route('login')->with('error', 'No se pudo completar el inicio de sesión con Google. Intenta nuevamente más tarde.');
        }
    }
}

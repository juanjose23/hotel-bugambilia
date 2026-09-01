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
        Log::info('[GoogleAuth] Iniciando redirección a Google OAuth', [
            'url_solicitada' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'x_inertia' => $request->header('X-Inertia'),
            'client_id_configurado' => ! empty(config('services.google.client_id')),
            'client_id_preview' => substr(is_string(config('services.google.client_id')) ? config('services.google.client_id') : '', 0, 15).'...',
            'redirect_uri_configurado' => config('services.google.redirect'),
        ]);

        try {
            /** @var GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $targetUrl = $driver->stateless()->redirect()->getTargetUrl();

            Log::info('[GoogleAuth] URL generada para Google OAuth', [
                'target_url' => $targetUrl,
            ]);

            if ($request->header('X-Inertia') !== null) {
                Log::info('[GoogleAuth] Retornando Inertia::location');

                return Inertia::location($targetUrl);
            }

            Log::info('[GoogleAuth] Redirección HTTP directa enviada');

            return redirect()->away($targetUrl);
        } catch (Throwable $e) {
            Log::error('[GoogleAuth] Error generando redirección a Google: '.$e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->with('error', 'Error al conectar con Google: '.$e->getMessage());
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        Log::info('[GoogleAuth] Callback recibido desde Google OAuth', [
            'query_params' => $request->all(),
            'has_code' => $request->has('code'),
            'has_error' => $request->has('error'),
            'error' => $request->input('error'),
            'error_description' => $request->input('error_description'),
        ]);

        try {
            /** @var GoogleProvider $driver */
            $driver = Socialite::driver('google');

            Log::info('[GoogleAuth] Solicitando datos de usuario a Google...');
            $googleUser = $driver->stateless()->user();

            Log::info('[GoogleAuth] Usuario obtenido de Google exitosamente', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'nickname' => $googleUser->getNickname(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            Log::info('[GoogleAuth] Procesando usuario en base de datos...');
            $usuario = $this->procesarGoogleCallback->ejecutar($googleUser);

            Log::info('[GoogleAuth] Usuario procesado con éxito', [
                'user_id' => $usuario->id,
                'email' => $usuario->email,
            ]);

            Auth::login($usuario, true);
            $request->session()->regenerate();

            Log::info('[GoogleAuth] Sesión iniciada exitosamente', [
                'user_id' => $usuario->id,
            ]);

            return redirect()->intended(route('home'))->with('exito', '¡Has iniciado sesión con Google exitosamente!');
        } catch (Throwable $e) {
            Log::error('[GoogleAuth] Error crítico en callback de Google: '.$e->getMessage(), [
                'exception' => get_class($e),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')->with('error', 'No se pudo completar el inicio de sesión con Google: '.$e->getMessage());
        }
    }
}

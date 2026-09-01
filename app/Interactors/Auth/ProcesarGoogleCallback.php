<?php

declare(strict_types=1);

namespace App\Interactors\Auth;

use App\Interactors\Usuarios\Clientes\RegistrarClienteNuevo;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\SocialAccount;
use App\Repository\Queries\Catalogos\ObtenerCatalogoClienteRegularQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

final readonly class ProcesarGoogleCallback
{
    public function __construct(
        private RegistrarClienteNuevo $registrarClienteNuevo,
        private ObtenerCatalogoClienteRegularQuery $catalogoRegularQuery,
    ) {}

    public function ejecutar(SocialiteUser $googleUser): User
    {
        return DB::transaction(function () use ($googleUser): User {
            $googleId = (string) $googleUser->getId();
            $email = $googleUser->getEmail();
            $avatar = $googleUser->getAvatar();
            $name = $googleUser->getName() ?? 'Huésped Google';

            Log::info('[ProcesarGoogleCallback] Buscando cuenta social existente', [
                'provider' => 'google',
                'provider_id' => $googleId,
            ]);

            // 1. Verificar si ya existe una cuenta social vinculada
            $cuentaSocial = SocialAccount::where('provider', 'google')
                ->where('provider_id', $googleId)
                ->first();

            if ($cuentaSocial !== null && $cuentaSocial->user !== null) {
                Log::info('[ProcesarGoogleCallback] Cuenta social encontrada y vinculada a usuario', [
                    'user_id' => $cuentaSocial->user->id,
                    'email' => $cuentaSocial->user->email,
                ]);

                // Actualizar avatar si cambió
                if ($avatar !== null && $cuentaSocial->avatar !== $avatar) {
                    $cuentaSocial->update(['avatar' => $avatar]);
                }

                return $cuentaSocial->user;
            }

            Log::info('[ProcesarGoogleCallback] No existe cuenta social previa. Buscando usuario por email', [
                'email' => $email,
            ]);
            $usuarioExistente = null;
            if ($email !== null && $email !== '') {
                $usuarioExistente = User::where('email', $email)->first();
            }

            if ($usuarioExistente !== null) {
                Log::info('[ProcesarGoogleCallback] Usuario encontrado por email. Vinculando cuenta social', [
                    'user_id' => $usuarioExistente->id,
                    'email' => $usuarioExistente->email,
                ]);

                // Vincular cuenta social a usuario existente
                SocialAccount::create([
                    'user_id' => $usuarioExistente->id,
                    'provider' => 'google',
                    'provider_id' => $googleId,
                    'provider_email' => $email,
                    'avatar' => $avatar,
                    'provider_data' => [
                        'name' => $name,
                        'nickname' => $googleUser->getNickname(),
                    ],
                ]);

                return $usuarioExistente;
            }

            // 3. Crear nuevo cliente y usuario
            $nombresPartes = explode(' ', trim($name));
            $primerNombre = $nombresPartes[0] !== '' ? $nombresPartes[0] : 'Huésped';
            $primerApellido = count($nombresPartes) > 1 ? end($nombresPartes) : 'Google';

            Log::info('[ProcesarGoogleCallback] Creando nuevo cliente y usuario para Google Auth', [
                'primer_nombre' => $primerNombre,
                'primer_apellido' => $primerApellido,
                'email' => $email,
            ]);

            $catalogo = $this->catalogoRegularQuery->obtener();

            $datosNuevoCliente = [
                'tipo_persona' => 'natural',
                'catalogo_id' => $catalogo->id ?? 1,
                'primer_nombre' => $primerNombre,
                'primer_apellido' => $primerApellido,
                'email' => $email ?? "google_{$googleId}@hotelbugambilia.com",
                'telefono' => null,
                'tipo_identificacion' => null,
                'numero_identificacion' => null,
                'identificacion' => null,
                'password' => Str::random(32),
            ];

            $resultado = $this->registrarClienteNuevo->ejecutar($datosNuevoCliente, true);

            $nuevoUsuario = $resultado['user'];

            if (! $nuevoUsuario instanceof User) {
                throw new \RuntimeException('No se pudo generar la cuenta de usuario para Google Login.');
            }

            Log::info('[ProcesarGoogleCallback] Nuevo usuario creado exitosamente. Registrando SocialAccount', [
                'user_id' => $nuevoUsuario->id,
            ]);

            // Registrar cuenta social
            SocialAccount::create([
                'user_id' => $nuevoUsuario->id,
                'provider' => 'google',
                'provider_id' => $googleId,
                'provider_email' => $email,
                'avatar' => $avatar,
                'provider_data' => [
                    'name' => $name,
                    'nickname' => $googleUser->getNickname(),
                ],
            ]);

            return $nuevoUsuario;
        });
    }
}

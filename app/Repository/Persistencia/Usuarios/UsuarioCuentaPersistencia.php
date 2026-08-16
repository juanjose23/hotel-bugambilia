<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class UsuarioCuentaPersistencia
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crearCliente(Persona $persona, array $datos): User
    {
        return User::create([
            'persona_id' => $persona->id,
            'name' => $persona->nombre_completo ?? $datos['name'] ?? $datos['primer_nombre'] ?? 'Cliente',
            'email' => $this->email($datos),
            'password' => Hash::make($this->password($datos)),
            'is_admin' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarPasswordSiFueProvista(User $user, array $datos): void
    {
        $password = $datos['password'] ?? null;

        if (! is_string($password) || trim($password) === '') {
            return;
        }

        $user->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function email(array $datos): ?string
    {
        $email = $datos['email'] ?? null;

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return trim($email);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function password(array $datos): string
    {
        $password = $datos['password'] ?? null;

        if (is_string($password) && trim($password) !== '') {
            return $password;
        }

        return Str::random(32);
    }
}

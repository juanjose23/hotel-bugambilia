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
            'password_change_required' => (bool) ($datos['password_change_required'] ?? false),
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

    public function cambiarContrasena(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
            'password_change_required' => false,
        ]);
    }

    public function buscarPorEmail(string $email): ?User
    {
        return User::query()->where('email', trim($email))->first();
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizarDatosBasicos(User $user, array $datos): void
    {
        $user->update($datos);
    }

    public function asociarPersona(User $user, Persona $persona): void
    {
        $user->persona()->associate($persona);
        $user->save();
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

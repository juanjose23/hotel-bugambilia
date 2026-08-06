<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class VincularPersonaExistenteAUser
{
    /**
     * Crea un User vinculado a una Persona existente.
     *
     * @param  array<string, mixed>  $datos
     */
    public function ejecutar(Persona $persona, array $datos): User
    {
        return DB::transaction(function () use ($persona, $datos): User {
            $this->actualizarContactoSiEsNecesario($persona, $datos);

            $usuarioExistente = User::where('persona_id', $persona->id)
                ->orWhere('email', $datos['email'] ?? '')
                ->first();

            if ($usuarioExistente instanceof User) {
                if (isset($datos['password']) && is_string($datos['password']) && trim($datos['password']) !== '') {
                    $usuarioExistente->update([
                        'password' => Hash::make($datos['password']),
                    ]);
                }

                return $usuarioExistente;
            }

            return User::create([
                'persona_id' => $persona->id,
                'name' => $persona->nombre_completo ?? $datos['name'] ?? 'Cliente',
                'email' => isset($datos['email']) && is_string($datos['email']) && trim($datos['email']) !== '' ? trim($datos['email']) : null,
                'password' => Hash::make(isset($datos['password']) && is_string($datos['password']) && trim($datos['password']) !== '' ? $datos['password'] : Str::random(32)),
                'is_admin' => false,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function actualizarContactoSiEsNecesario(Persona $persona, array $datos): void
    {
        $cambios = [];

        if (isset($datos['telefono']) && $datos['telefono'] !== $persona->telefono) {
            $cambios['telefono'] = $datos['telefono'];
        }

        if (isset($datos['direccion']) && $datos['direccion'] !== $persona->direccion) {
            $cambios['direccion'] = $datos['direccion'];
        }

        if ($cambios !== []) {
            $persona->update($cambios);
        }
    }
}

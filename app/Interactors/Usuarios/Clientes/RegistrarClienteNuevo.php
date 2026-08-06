<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Clientes;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class RegistrarClienteNuevo
{
    /**
     * Crea Persona + PersonaNatural/PersonaJuridica + Cliente + [User opcional] en transacción.
     *
     * @param  array<string, mixed>  $datos
     * @return array{persona: Persona, cliente: Cliente, user: User|null}
     */
    public function ejecutar(array $datos, bool $crearUsuario = true): array
    {
        return DB::transaction(function () use ($datos, $crearUsuario): array {
            $tipoPersona = $datos['tipo_persona'] ?? 'natural';

            $persona = Persona::create([
                'primer_nombre' => $datos['primer_nombre'],
                'segundo_nombre' => $datos['segundo_nombre'] ?? null,
                'tipo_persona' => $tipoPersona,
                'telefono' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'pais_id' => $datos['pais_id'] ?? null,
            ]);

            if ($tipoPersona === 'juridica') {
                PersonaJuridica::create([
                    'persona_id' => $persona->id,
                    'razon_social' => $datos['razon_social'] ?? $datos['primer_nombre'],
                    'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                    'numero_identificacion' => $datos['numero_identificacion'] ?? null,
                    'fecha_constitucion' => $datos['fecha_nacimiento'] ?? null,
                ]);
            } else {
                PersonaNatural::create([
                    'persona_id' => $persona->id,
                    'primer_apellido' => $datos['primer_apellido'] ?? '',
                    'segundo_apellido' => $datos['segundo_apellido'] ?? null,
                    'tipo_identificacion' => $datos['tipo_identificacion'] ?? null,
                    'numero_identificacion' => $datos['numero_identificacion'] ?? null,
                    'sexo' => $datos['sexo'] ?? null,
                    'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                ]);
            }

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'catalogo_id' => $datos['catalogo_id'],
                'estado' => 1,
            ]);

            $user = null;

            if ($crearUsuario) {
                $user = User::create([
                    'persona_id' => $persona->id,
                    'name' => $persona->nombre_completo ?? $datos['primer_nombre'],
                    'email' => isset($datos['email']) && is_string($datos['email']) && trim($datos['email']) !== '' ? trim($datos['email']) : null,
                    'password' => Hash::make(isset($datos['password']) && is_string($datos['password']) && trim($datos['password']) !== '' ? $datos['password'] : Str::random(32)),
                    'is_admin' => false,
                ]);
            }

            $personaRefrescada = $persona->fresh();

            if (! $personaRefrescada instanceof Persona) {
                throw new \RuntimeException('No se pudo refrescar la persona.');
            }

            return [
                'persona' => $personaRefrescada,
                'cliente' => $cliente,
                'user' => $user,
            ];
        });
    }
}

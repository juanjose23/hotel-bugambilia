<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use Illuminate\Support\Facades\DB;

final class ActualizarPerfilClientePortal
{
    /**
     * @param  array{nombre: string, email: string, telefono?: string|null, identificacion?: string|null, tipo_identificacion?: string|null, pais_id?: int|null}  $datos
     * @return array{id: int, nombre: string, email: string, telefono: string|null, identificacion: string|null}
     */
    public function ejecutar(User $user, array $datos): array
    {
        /** @var array{id: int, nombre: string, email: string, telefono: string|null, identificacion: string|null} */
        return DB::transaction(function () use ($user, $datos): array {
            $user->name = trim($datos['nombre']);
            $user->email = trim($datos['email']);
            $user->save();

            /** @var Persona|null $persona */
            $persona = $user->persona;
            if ($persona === null) {
                $persona = Persona::query()->create([
                    'tipo_persona' => 'natural',
                    'primer_nombre' => $user->name,
                    'telefono' => $datos['telefono'] ?? null,
                ]);
                $user->persona()->associate($persona);
                $user->save();
            } else {
                $persona->update([
                    'primer_nombre' => $user->name,
                    'telefono' => $datos['telefono'] ?? $persona->telefono,
                ]);
            }

            return [
                'id' => (int) $user->id,
                'nombre' => (string) $user->name,
                'email' => (string) $user->email,
                'telefono' => $persona->telefono,
                'identificacion' => $datos['identificacion'] ?? null,
            ];
        });
    }
}

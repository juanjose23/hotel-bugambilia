<?php

declare(strict_types=1);

namespace App\Interactors\Clientes;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Persistencia\Usuarios\PersonaPersistencia;
use App\Repository\Persistencia\Usuarios\UsuarioCuentaPersistencia;
use Illuminate\Support\Facades\DB;

final readonly class ActualizarPerfilClientePortal
{
    public function __construct(
        private PersonaPersistencia $personaPersistencia,
        private UsuarioCuentaPersistencia $usuarioPersistencia,
    ) {}

    /**
     * @param  array{nombre: string, email: string, telefono?: string|null, identificacion?: string|null, tipo_identificacion?: string|null, pais_id?: int|null}  $datos
     * @return array{id: int, nombre: string, email: string, telefono: string|null, identificacion: string|null}
     */
    public function ejecutar(User $user, array $datos): array
    {
        return DB::transaction(function () use ($user, $datos): array {
            $this->usuarioPersistencia->actualizarDatosBasicos($user, [
                'name' => trim($datos['nombre']),
                'email' => trim($datos['email']),
            ]);

            /** @var Persona|null $persona */
            $persona = $user->persona;
            if ($persona === null) {
                $persona = $this->personaPersistencia->crearConIdentidad([
                    'tipo_persona' => 'natural',
                    'primer_nombre' => $user->name,
                    'telefono' => $datos['telefono'] ?? null,
                ]);
                $this->usuarioPersistencia->asociarPersona($user, $persona);
            } else {
                $this->personaPersistencia->actualizarDatosBasicos($persona, [
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

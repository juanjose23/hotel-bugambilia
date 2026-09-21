<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Events\Usuarios\ClienteRegistrado;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use App\Repository\Persistencia\Usuarios\ClientePersistencia;
use App\Repository\Persistencia\Usuarios\ConflictoIdentidadPersistencia;
use Illuminate\Support\Facades\DB;

final readonly class ResolverConflictoIdentidad
{
    public function __construct(
        private VincularPersonaAUser $vincularAUser,
        private ClientePersistencia $clientes,
        private ConflictoIdentidadPersistencia $conflictoPersistencia,
    ) {}

    /**
     * Resuelve un conflicto de identidad vinculando la persona.
     *
     * @param  array<string, mixed>  $datos
     * @param  int|null  $usuarioId  Usuario autenticado que resuelve el conflicto (auditoría).
     * @return array{cliente: Cliente, persona: Persona, user: User}
     */
    public function vincular(ConflictoIdentidad $conflicto, array $datos, ?int $usuarioId = null): array
    {
        return DB::transaction(function () use ($conflicto, $datos, $usuarioId): array {
            $persona = $conflicto->persona;

            if ($persona === null) {
                throw new \RuntimeException('El conflicto no tiene persona asociada.');
            }

            $cliente = $this->clientes->crearDesdePersona($persona, $datos);

            $user = $this->vincularAUser->ejecutar($persona, $datos);

            $notas = isset($datos['notas']) && is_string($datos['notas']) ? $datos['notas'] : null;
            $this->conflictoPersistencia->resolver($conflicto, $notas, $usuarioId);

            ClienteRegistrado::dispatch($cliente, $persona, false);

            $personaRefrescada = $persona->fresh();

            if (! $personaRefrescada instanceof Persona) {
                throw new \RuntimeException('No se pudo refrescar la persona.');
            }

            return [
                'cliente' => $cliente,
                'persona' => $personaRefrescada,
                'user' => $user,
            ];
        });
    }

    public function rechazar(ConflictoIdentidad $conflicto, string $notas, ?int $usuarioId = null): void
    {
        $this->conflictoPersistencia->rechazar($conflicto, $notas, $usuarioId);
    }
}

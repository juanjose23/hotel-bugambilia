<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Identidad;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Events\Usuarios\ClienteRegistrado;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class ResolverConflictoIdentidad
{
    public function __construct(
        private readonly VincularPersonaExistenteAUser $vincularExistente,
    ) {}

    /**
     * Resuelve un conflicto de identidad vinculando la persona.
     *
     * @param  array<string, mixed>  $datos
     * @return array{cliente: Cliente, persona: Persona, user: User}
     */
    public function vincular(ConflictoIdentidad $conflicto, array $datos): array
    {
        return DB::transaction(function () use ($conflicto, $datos): array {
            $persona = $conflicto->persona;

            if ($persona === null) {
                throw new \RuntimeException('El conflicto no tiene persona asociada.');
            }

            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'catalogo_id' => $datos['catalogo_id'],
                'estado' => 1,
            ]);

            $user = $this->vincularExistente->ejecutar($persona, $datos);

            $conflicto->update([
                'estado' => EstadoConflictoIdentidad::Resuelto,
                'resuelto_por' => Auth::id(),
                'resuelto_en' => now(),
                'notas' => $datos['notas'] ?? 'Vinculado manualmente por administrador.',
            ]);

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

    public function rechazar(ConflictoIdentidad $conflicto, string $notas): void
    {
        $conflicto->update([
            'estado' => EstadoConflictoIdentidad::Rechazado,
            'resuelto_por' => Auth::id(),
            'resuelto_en' => now(),
            'notas' => $notas,
        ]);
    }
}

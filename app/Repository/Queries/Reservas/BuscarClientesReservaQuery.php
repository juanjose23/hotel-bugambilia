<?php

declare(strict_types=1);

namespace App\Repository\Queries\Reservas;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Illuminate\Database\Eloquent\Builder;

final class BuscarClientesReservaQuery
{
    /** @return array<int, string> */
    public function buscar(string $termino, int $limite = 30): array
    {
        return $this->queryBase()
            ->where(function ($query) use ($termino): void {
                $patron = "%{$termino}%";
                $query
                    ->where('name', 'like', $patron)
                    ->orWhere('email', 'like', $patron)
                    ->orWhereHas('persona', fn ($persona) => $persona
                        ->where('primer_nombre', 'like', $patron)
                        ->orWhere('segundo_nombre', 'like', $patron)
                        ->orWhere('telefono', 'like', $patron))
                    ->orWhereHas('persona.personaNatural', fn ($pn) => $pn
                        ->where('primer_apellido', 'like', $patron)
                        ->orWhere('segundo_apellido', 'like', $patron)
                        ->orWhere('numero_identificacion', 'like', $patron));
            })
            ->limit($limite)
            ->get()
            ->mapWithKeys(fn (User $usuario): array => [
                $usuario->id => $this->etiqueta($usuario),
            ])
            ->all();
    }

    public function etiquetaPorId(int $usuarioId): ?string
    {
        /** @var User|null $usuario */
        $usuario = $this->queryBase()->find($usuarioId);

        return $usuario instanceof User ? $this->etiqueta($usuario) : null;
    }

    /** @return array{nombre: string, telefono: string|null, email: string|null}|null */
    public function datosPorId(int $usuarioId): ?array
    {
        /** @var User|null $usuario */
        $usuario = $this->queryBase()->find($usuarioId);

        if (! $usuario instanceof User) {
            return null;
        }

        $persona = $usuario->getRelation('persona');

        return [
            'nombre' => $persona instanceof Persona
                ? ObtenerNombrePersona::desde($persona)
                : $usuario->name,
            'telefono' => $persona instanceof Persona ? $persona->telefono : null,
            'email' => $usuario->email,
        ];
    }

    private function etiqueta(User $usuario): string
    {
        $persona = $usuario->getRelation('persona');
        $nombre = $persona instanceof Persona
            ? ObtenerNombrePersona::desde($persona)
            : $usuario->name;

        $telefono = $usuario->persona?->telefono;

        return $telefono !== null && $telefono !== ''
            ? "{$nombre} · {$telefono}"
            : $nombre;
    }

    /** @return Builder<User> */
    private function queryBase(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->whereHas('persona.cliente')
            ->whereDoesntHave('persona.colaborador')
            ->whereDoesntHave('persona.proveedor')
            ->with('persona');
    }
}

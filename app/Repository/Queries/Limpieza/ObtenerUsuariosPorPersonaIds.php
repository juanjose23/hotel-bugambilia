<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class ObtenerUsuariosPorPersonaIds
{
    /**
     * @param  array<int, mixed>  $personaIds
     * @return Collection<int, User>
     */
    public function ejecutar(array $personaIds): Collection
    {
        $personaIds = array_values(array_filter(
            $personaIds,
            fn (mixed $personaId): bool => is_int($personaId),
        ));

        if ($personaIds === []) {
            return collect();
        }

        return User::query()
            ->whereIn('persona_id', $personaIds)
            ->get();
    }
}

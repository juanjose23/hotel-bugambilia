<?php

declare(strict_types=1);

namespace App\Repository\Queries\Usuarios;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class ObtenerUsuariosPorRolesQuery
{
    /**
     * @param  array<int, string>  $roles
     * @return Collection<int, User>
     */
    public function ejecutar(array $roles, ?User $creador = null): Collection
    {
        $usuarios = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->get();

        if ($creador !== null && ! $usuarios->contains('id', $creador->id)) {
            $usuarios->push($creador);
        }

        return $usuarios;
    }
}

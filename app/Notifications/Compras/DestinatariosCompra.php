<?php

declare(strict_types=1);

namespace App\Notifications\Compras;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class DestinatariosCompra
{
    /** @return Collection<int, User> */
    public function obtener(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'compras_encargado',
            'compras_aprobador',
            'administrador',
        ];

        $usuarios = User::whereHas(
            'roles',
            fn ($q) => $q->whereIn('name', $roles),
        )->get();

        if ($creador && ! $usuarios->contains('id', $creador->id)) {
            $usuarios->push($creador);
        }

        return $usuarios;
    }
}

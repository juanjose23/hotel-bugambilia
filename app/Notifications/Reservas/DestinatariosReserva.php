<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class DestinatariosReserva
{
    /** @return Collection<int, User> */
    public function obtener(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'recepcion_encargado',
            'recepcion_supervisor',
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

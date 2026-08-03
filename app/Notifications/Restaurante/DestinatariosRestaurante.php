<?php

declare(strict_types=1);

namespace App\Notifications\Restaurante;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class DestinatariosRestaurante
{
    /** @return Collection<int, User> */
    public function obtenerCocina(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'restaurante_cocina',
            'restaurante_encargado',
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

    /** @return Collection<int, User> */
    public function obtenerMeseros(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'restaurante_mesero',
            'restaurante_encargado',
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

    /** @return Collection<int, User> */
    public function obtenerTodos(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'restaurante_cocina',
            'restaurante_mesero',
            'restaurante_encargado',
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

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

        $usuarios = User::query()
            ->where(function ($query) use ($roles): void {
                $query
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
                    ->orWhereHas('permissions', fn ($q) => $q->whereIn('name', [
                        'ViewAny:Solicitud',
                        'View:Solicitud',
                        'Inventario:ResolverAbastecimientoCocina',
                    ]));
            })
            ->get();

        if ($creador && ! $usuarios->contains('id', $creador->id)) {
            $usuarios->push($creador);
        }

        return $usuarios;
    }

    /** @return Collection<int, User> */
    public function obtenerComprasEInventario(?User $creador = null): Collection
    {
        $roles = [
            'super_admin',
            'compras_encargado',
            'compras_aprobador',
            'inventario_encargado',
            'inventario_responsable',
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

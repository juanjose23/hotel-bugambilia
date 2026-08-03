<?php

declare(strict_types=1);

namespace App\Notifications\Reservas;

use App\Repository\Models\User;
use App\Repository\Queries\Usuarios\ObtenerUsuariosPorRolesQuery;
use Illuminate\Support\Collection;

final class DestinatariosReserva
{
    /** Roles que reciben notificaciones de reservas. */
    private const ROLES = [
        'super_admin',
        'recepcion_encargado',
        'recepcion_supervisor',
        'administrador',
    ];

    public function __construct(
        private readonly ObtenerUsuariosPorRolesQuery $usuariosPorRoles,
    ) {}

    /** @return Collection<int, User> */
    public function obtener(?User $creador = null): Collection
    {
        return $this->usuariosPorRoles->ejecutar(self::ROLES, $creador);
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\User;

final class ObtenerColaboradorPorUsuarioQuery
{
    public function ejecutar(int $usuarioId): ?Colaborador
    {
        /** @var User|null $usuario */
        $usuario = User::query()->find($usuarioId);

        if ($usuario === null || $usuario->persona_id === null) {
            return null;
        }

        /** @var Colaborador|null $colaborador */
        $colaborador = Colaborador::query()
            ->where('persona_id', $usuario->persona_id)
            ->first();

        return $colaborador;
    }
}

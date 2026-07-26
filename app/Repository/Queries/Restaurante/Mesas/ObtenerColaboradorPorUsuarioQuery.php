<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Mesas;

use App\Repository\Models\Colaboradores\Colaborador;

final class ObtenerColaboradorPorUsuarioQuery
{
    public function ejecutar(int $usuarioId): ?Colaborador
    {
        /** @var Colaborador|null */
        return Colaborador::query()
            ->where('persona_id', $usuarioId)
            ->first();
    }
}

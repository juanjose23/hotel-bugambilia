<?php

declare(strict_types=1);

namespace App\Notifications\Reportes\Shared;

use App\Repository\Models\User;
use Illuminate\Support\Collection;

final class DestinatariosReporte
{
    /** @return Collection<int, User> */
    public function obtener(?User $usuario = null): Collection
    {
        if ($usuario) {
            return collect([$usuario]);
        }

        return collect();
    }
}

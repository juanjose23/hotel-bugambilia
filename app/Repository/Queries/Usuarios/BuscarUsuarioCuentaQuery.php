<?php

declare(strict_types=1);

namespace App\Repository\Queries\Usuarios;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;

final class BuscarUsuarioCuentaQuery
{
    public function porPersona(Persona $persona): ?User
    {
        return User::query()
            ->whereBelongsTo($persona)
            ->first();
    }

    public function porEmail(string $email): ?User
    {
        $emailNormalizado = trim($email);

        if ($emailNormalizado === '') {
            return null;
        }

        return User::query()
            ->where('email', $emailNormalizado)
            ->first();
    }

    public function porId(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function existePorName(string $name): bool
    {
        return User::query()->where('name', $name)->exists();
    }
}

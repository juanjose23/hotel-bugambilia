<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Auth;

final class UsuarioAutenticado
{
    /**
     * Devuelve el id del usuario autenticado como entero, o null si no hay sesión.
     */
    public static function id(): ?int
    {
        $id = Auth::id();

        return is_int($id) ? $id : null;
    }
}

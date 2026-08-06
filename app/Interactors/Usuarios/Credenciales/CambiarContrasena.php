<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Credenciales;

use App\Repository\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class CambiarContrasena
{
    public function ejecutar(User $usuario, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $usuario->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $usuario->update([
            'password' => Hash::make($newPassword),
            'password_change_required' => false,
        ]);
    }
}

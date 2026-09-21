<?php

declare(strict_types=1);

namespace App\Interactors\Usuarios\Credenciales;

use App\Repository\Models\User;
use App\Repository\Persistencia\Usuarios\UsuarioCuentaPersistencia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final readonly class CambiarContrasena
{
    public function __construct(
        private UsuarioCuentaPersistencia $persistencia,
    ) {}

    public function ejecutar(User $usuario, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $usuario->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual no es correcta.'],
            ]);
        }

        $this->persistencia->cambiarContrasena($usuario, $newPassword);
    }
}

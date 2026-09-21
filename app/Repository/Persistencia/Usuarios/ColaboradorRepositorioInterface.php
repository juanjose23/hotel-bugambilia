<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Repository\Models\Colaboradores\ColaboradorSalario;

interface ColaboradorRepositorioInterface
{
    public function desactivarSalarioActivo(int $colaboradorId): void;

    /** @param array<string, mixed> $datos */
    public function crearSalario(array $datos): ColaboradorSalario;
}

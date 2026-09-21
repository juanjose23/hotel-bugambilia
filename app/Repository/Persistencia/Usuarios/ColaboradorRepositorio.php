<?php

declare(strict_types=1);

namespace App\Repository\Persistencia\Usuarios;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\ColaboradorSalario;

final readonly class ColaboradorRepositorio implements ColaboradorRepositorioInterface
{
    public function desactivarSalarioActivo(int $colaboradorId): void
    {
        ColaboradorSalario::where('colaborador_id', $colaboradorId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->update(['estado' => EstadoGeneral::Inactivo->value]);
    }

    /** @param array<string, mixed> $datos */
    public function crearSalario(array $datos): ColaboradorSalario
    {
        return ColaboradorSalario::create($datos);
    }
}

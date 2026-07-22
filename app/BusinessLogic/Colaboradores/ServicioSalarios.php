<?php

declare(strict_types=1);

namespace App\BusinessLogic\Colaboradores;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\ColaboradorSalario;

class ServicioSalarios
{
    public function desactivarSalarioActivo(int $colaboradorId): void
    {
        ColaboradorSalario::where('colaborador_id', $colaboradorId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->update(['estado' => EstadoGeneral::Inactivo->value]);
    }
}

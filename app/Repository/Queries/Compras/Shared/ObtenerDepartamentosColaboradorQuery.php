<?php

declare(strict_types=1);

namespace App\Repository\Queries\Compras\Shared;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\ColaboradorCargoHistorial;

final class ObtenerDepartamentosColaboradorQuery
{
    /** @return array<int|string, string> */
    public function ejecutar(int $colaboradorId): array
    {
        $result = ColaboradorCargoHistorial::where('colaborador_id', $colaboradorId)
            ->where('estado', EstadoGeneral::Activo->value)
            ->whereNull('fecha_fin')
            ->with('departamento')
            ->get()
            ->pluck('departamento.nombre', 'departamento.id')
            ->filter()
            ->toArray();

        /** @var array<int|string, string> $result */
        return $result;
    }
}

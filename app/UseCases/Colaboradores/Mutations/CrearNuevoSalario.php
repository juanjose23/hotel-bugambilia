<?php

namespace App\UseCases\Colaboradores\Mutations;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Models\Colaboradores\ColaboradorSalario;
use Illuminate\Support\Facades\DB;

class CrearNuevoSalario
{
    /** @param array<string, mixed> $data */
    public function __invoke(int $colaboradorId, array $data): ColaboradorSalario
    {
        return DB::transaction(function () use ($colaboradorId, $data) {
            $estado = EstadoCatalogo::fromValue($data['estado'] ?? EstadoCatalogo::Activo->value)->value;

            if ($estado === EstadoCatalogo::Activo->value) {
                ColaboradorSalario::where('colaborador_id', $colaboradorId)
                    ->where('estado', EstadoCatalogo::Activo->value)
                    ->update(['estado' => EstadoCatalogo::Inactivo->value]);
            }

            return ColaboradorSalario::create([
                'colaborador_id' => $colaboradorId,
                'salario' => $data['salario'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'estado' => $estado,
            ]);
        });
    }
}

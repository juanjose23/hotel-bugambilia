<?php

declare(strict_types=1);

namespace App\Interactors\Colaboradores;

use App\BusinessLogic\Colaboradores\ServicioSalarios;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\ColaboradorSalario;
use Illuminate\Support\Facades\DB;

class CrearNuevoSalario
{
    public function __construct(
        private readonly ServicioSalarios $servicioSalarios,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(int $colaboradorId, array $data): ColaboradorSalario
    {
        return DB::transaction(function () use ($colaboradorId, $data) {
            $rawEst = $data['estado'] ?? EstadoGeneral::Activo->value;
            $estadoInt = is_numeric($rawEst) ? (int) $rawEst : EstadoGeneral::Activo->value;
            $estado = EstadoGeneral::tryFrom($estadoInt) ?? EstadoGeneral::Activo;
            $estadoValue = $estado->value;

            if ($estado === EstadoGeneral::Activo) {
                $this->servicioSalarios->desactivarSalarioActivo($colaboradorId);
            }

            return ColaboradorSalario::create([
                'colaborador_id' => $colaboradorId,
                'salario' => $data['salario'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'estado' => $estadoValue,
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function __invoke(int $colaboradorId, array $data): ColaboradorSalario
    {
        return $this->execute($colaboradorId, $data);
    }
}

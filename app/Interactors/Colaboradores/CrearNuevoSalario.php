<?php

declare(strict_types=1);

namespace App\Interactors\Colaboradores;

use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Colaboradores\ColaboradorSalario;
use App\Repository\Persistencia\Usuarios\ColaboradorRepositorioInterface;
use Illuminate\Support\Facades\DB;

final readonly class CrearNuevoSalario
{
    public function __construct(
        private ColaboradorRepositorioInterface $colaboradorRepositorio,
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
                $this->colaboradorRepositorio->desactivarSalarioActivo($colaboradorId);
            }

            return $this->colaboradorRepositorio->crearSalario([
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

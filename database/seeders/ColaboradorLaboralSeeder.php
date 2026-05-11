<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColaboradorLaboralSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->registros() as $registro) {
            $colaboradorId = $this->colaboradorId($registro['codigo']);

            if ($colaboradorId === null) {
                continue;
            }

            DB::table('colaborador_salarios')->updateOrInsert(
                ['colaborador_id' => $colaboradorId, 'estado' => 1],
                [
                    'salario' => $registro['salario'],
                    'fecha_inicio' => $registro['fecha_inicio'],
                    'fecha_fin' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('colaborador_cargos_historial')->updateOrInsert(
                [
                    'colaborador_id' => $colaboradorId,
                    'cargo_id' => $this->catalogoId($registro['cargo_codigo']),
                ],
                [
                    'departamento_id' => $this->catalogoId($registro['departamento_codigo']),
                    'fecha_inicio' => $registro['fecha_inicio'],
                    'fecha_fin' => null,
                    'estado' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            foreach ($registro['documentos'] as $documento) {
                DB::table('colaborador_documentos')->updateOrInsert(
                    [
                        'colaborador_id' => $colaboradorId,
                        'tipo' => $documento['tipo'],
                    ],
                    [
                        'archivo' => $documento['archivo'],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function registros(): array
    {
        return [
            [
                'codigo' => 'COL-0001',
                'salario' => 18500.00,
                'fecha_inicio' => '2024-02-10',
                'cargo_codigo' => 'CAR_RECEP_SR',
                'departamento_codigo' => 'DEP_RECEPCION',
                'documentos' => [
                    [
                        'tipo' => 'identificacion',
                        'archivo' => 'colaboradores/col-0001/identificacion.pdf',
                    ],
                    [
                        'tipo' => 'contrato',
                        'archivo' => 'colaboradores/col-0001/contrato-laboral.pdf',
                    ],
                ],
            ],
            [
                'codigo' => 'COL-0002',
                'salario' => 29500.00,
                'fecha_inicio' => '2023-08-15',
                'cargo_codigo' => 'CAR_GERENTE_OPS',
                'departamento_codigo' => 'DEP_OPERACIONES',
                'documentos' => [
                    [
                        'tipo' => 'identificacion',
                        'archivo' => 'colaboradores/col-0002/identificacion.pdf',
                    ],
                    [
                        'tipo' => 'certificado-medico',
                        'archivo' => 'colaboradores/col-0002/certificado-medico.pdf',
                    ],
                ],
            ],
            [
                'codigo' => 'COL-0003',
                'salario' => 34000.00,
                'fecha_inicio' => '2022-01-20',
                'cargo_codigo' => 'CAR_GERENTE_ADM',
                'departamento_codigo' => 'DEP_OPERACIONES',
                'documentos' => [
                    [
                        'tipo' => 'identificacion',
                        'archivo' => 'colaboradores/col-0003/identificacion.pdf',
                    ],
                    [
                        'tipo' => 'expediente',
                        'archivo' => 'colaboradores/col-0003/expediente.pdf',
                    ],
                ],
            ],
        ];
    }

    private function colaboradorId(string $codigo): ?int
    {
        $colaboradorId = DB::table('colaboradores')
            ->where('codigo', $codigo)
            ->value('id');

        return $colaboradorId !== null ? (int) $colaboradorId : null;
    }

    private function catalogoId(string $codigo): int
    {
        $catalogoId = DB::table('catalogos')
            ->where('codigo', $codigo)
            ->value('id');

        if ($catalogoId === null) {
            throw new \RuntimeException("No se encontro el catalogo requerido: {$codigo}");
        }

        return (int) $catalogoId;
    }
}

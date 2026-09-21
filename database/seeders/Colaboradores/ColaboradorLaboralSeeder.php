<?php

declare(strict_types=1);

namespace Database\Seeders\Colaboradores;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ColaboradorLaboralSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->historialCargos() as $registro) {
            $colaboradorId = $this->colaboradorId($this->texto($registro['codigo'] ?? ''));

            if ($colaboradorId === null) {
                continue;
            }

            DB::table('colaborador_cargos_historial')->updateOrInsert(
                [
                    'colaborador_id' => $colaboradorId,
                    'cargo_id' => $this->catalogoId($this->texto($registro['cargo_codigo'] ?? '')),
                    'estado' => 0,
                ],
                [
                    'departamento_id' => $this->catalogoId($this->texto($registro['departamento_codigo'] ?? '')),
                    'fecha_inicio' => $registro['fecha_inicio'] ?? now(),
                    'fecha_fin' => $registro['fecha_fin'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        foreach ($this->historialSalarios() as $registro) {
            $colaboradorId = $this->colaboradorId($this->texto($registro['codigo'] ?? ''));

            if ($colaboradorId === null) {
                continue;
            }

            DB::table('colaborador_salarios')->updateOrInsert(
                [
                    'colaborador_id' => $colaboradorId,
                    'estado' => 0,
                    'fecha_inicio' => $registro['fecha_inicio'] ?? now(),
                ],
                [
                    'salario' => $registro['salario'] ?? 0,
                    'fecha_fin' => $registro['fecha_fin'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        foreach ($this->registros() as $registro) {
            $codigoVal = $registro['codigo'] ?? '';
            $codigo = is_string($codigoVal) ? $codigoVal : '';
            $colaboradorId = $this->colaboradorId($codigo);

            if ($colaboradorId === null) {
                continue;
            }

            DB::table('colaborador_salarios')->updateOrInsert(
                ['colaborador_id' => $colaboradorId, 'estado' => 1],
                [
                    'salario' => $registro['salario'] ?? 0,
                    'fecha_inicio' => $registro['fecha_inicio'] ?? now(),
                    'fecha_fin' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $cargoCodVal = $registro['cargo_codigo'] ?? '';
            $cargoCod = is_string($cargoCodVal) ? $cargoCodVal : '';

            $deptCodVal = $registro['departamento_codigo'] ?? '';
            $deptCod = is_string($deptCodVal) ? $deptCodVal : '';

            DB::table('colaborador_cargos_historial')->updateOrInsert(
                [
                    'colaborador_id' => $colaboradorId,
                    'cargo_id' => $this->catalogoId($cargoCod),
                ],
                [
                    'departamento_id' => $this->catalogoId($deptCod),
                    'fecha_inicio' => $registro['fecha_inicio'] ?? now(),
                    'fecha_fin' => null,
                    'estado' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $documentos = $registro['documentos'] ?? [];
            if (is_array($documentos)) {
                foreach ($documentos as $documento) {
                    if (is_array($documento)) {
                        $docTipoVal = $documento['tipo'] ?? '';
                        $docTipo = is_string($docTipoVal) ? $docTipoVal : '';

                        $docArchivoVal = $documento['archivo'] ?? '';
                        $docArchivo = is_string($docArchivoVal) ? $docArchivoVal : '';

                        DB::table('colaborador_documentos')->updateOrInsert(
                            [
                                'colaborador_id' => $colaboradorId,
                                'tipo' => $docTipo,
                            ],
                            [
                                'archivo' => $docArchivo,
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function historialCargos(): array
    {
        return [
            [
                'codigo' => 'COL-0001',
                'cargo_codigo' => 'CAR_RECEP_JR',
                'departamento_codigo' => 'DEP_RECEPCION',
                'fecha_inicio' => '2023-01-15',
                'fecha_fin' => '2024-06-30',
            ],
            [
                'codigo' => 'COL-0002',
                'cargo_codigo' => 'CAR_RECEP_JEFE',
                'departamento_codigo' => 'DEP_RECEPCION',
                'fecha_inicio' => '2022-08-01',
                'fecha_fin' => '2023-11-30',
            ],
            [
                'codigo' => 'COL-0003',
                'cargo_codigo' => 'CAR_GERENTE_OPS',
                'departamento_codigo' => 'DEP_OPERACIONES',
                'fecha_inicio' => '2021-03-10',
                'fecha_fin' => '2022-12-31',
            ],
            [
                'codigo' => 'COL-0006',
                'cargo_codigo' => 'CAR_CAMARERA',
                'departamento_codigo' => 'DEP_AMA_LLAVES',
                'fecha_inicio' => '2021-06-01',
                'fecha_fin' => '2023-03-31',
            ],
            [
                'codigo' => 'COL-0009',
                'cargo_codigo' => 'CAR_MANT_TEC',
                'departamento_codigo' => 'DEP_MANTENIMIENTO',
                'fecha_inicio' => '2021-11-15',
                'fecha_fin' => '2023-06-30',
            ],
            [
                'codigo' => 'COL-0010',
                'cargo_codigo' => 'CAR_COCINERO',
                'departamento_codigo' => 'DEP_COCINA',
                'fecha_inicio' => '2022-05-20',
                'fecha_fin' => '2024-02-29',
            ],
            [
                'codigo' => 'COL-0013',
                'cargo_codigo' => 'CAR_RECEP_SR',
                'departamento_codigo' => 'DEP_RECEPCION',
                'fecha_inicio' => '2021-09-01',
                'fecha_fin' => '2023-08-31',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function historialSalarios(): array
    {
        return [
            ['codigo' => 'COL-0001', 'salario' => 15000.00, 'fecha_inicio' => '2023-01-15', 'fecha_fin' => '2024-06-30'],
            ['codigo' => 'COL-0002', 'salario' => 24000.00, 'fecha_inicio' => '2022-08-01', 'fecha_fin' => '2023-11-30'],
            ['codigo' => 'COL-0003', 'salario' => 38000.00, 'fecha_inicio' => '2021-03-10', 'fecha_fin' => '2022-12-31'],
            ['codigo' => 'COL-0006', 'salario' => 14500.00, 'fecha_inicio' => '2021-06-01', 'fecha_fin' => '2023-03-31'],
            ['codigo' => 'COL-0009', 'salario' => 18500.00, 'fecha_inicio' => '2021-11-15', 'fecha_fin' => '2023-06-30'],
            ['codigo' => 'COL-0010', 'salario' => 23000.00, 'fecha_inicio' => '2022-05-20', 'fecha_fin' => '2024-02-29'],
            ['codigo' => 'COL-0013', 'salario' => 15000.00, 'fecha_inicio' => '2021-09-01', 'fecha_fin' => '2023-08-31'],
        ];
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
                'fecha_inicio' => '2024-07-01',
                'cargo_codigo' => 'CAR_RECEP_SR',
                'departamento_codigo' => 'DEP_RECEPCION',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0001/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0001/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0002',
                'salario' => 32000.00,
                'fecha_inicio' => '2023-12-01',
                'cargo_codigo' => 'CAR_GERENTE_OPS',
                'departamento_codigo' => 'DEP_OPERACIONES',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0002/identificacion.pdf'],
                    ['tipo' => 'certificado-medico', 'archivo' => 'colaboradores/col-0002/certificado-medico.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0003',
                'salario' => 45000.00,
                'fecha_inicio' => '2023-01-01',
                'cargo_codigo' => 'CAR_GERENTE_GRAL',
                'departamento_codigo' => 'DEP_OPERACIONES',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0003/identificacion.pdf'],
                    ['tipo' => 'expediente', 'archivo' => 'colaboradores/col-0003/expediente.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0004',
                'salario' => 24000.00,
                'fecha_inicio' => '2022-02-15',
                'cargo_codigo' => 'CAR_RECEP_JEFE',
                'departamento_codigo' => 'DEP_RECEPCION',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0004/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0004/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0005',
                'salario' => 14500.00,
                'fecha_inicio' => '2024-01-10',
                'cargo_codigo' => 'CAR_RECEP_JR',
                'departamento_codigo' => 'DEP_RECEPCION',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0005/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0005/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0006',
                'salario' => 20000.00,
                'fecha_inicio' => '2023-04-01',
                'cargo_codigo' => 'CAR_AMA_LLAVES_JEFE',
                'departamento_codigo' => 'DEP_AMA_LLAVES',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0006/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0006/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0007',
                'salario' => 12500.00,
                'fecha_inicio' => '2023-04-15',
                'cargo_codigo' => 'CAR_CAMARERA',
                'departamento_codigo' => 'DEP_AMA_LLAVES',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0007/identificacion.pdf'],
                    ['tipo' => 'certificado-medico', 'archivo' => 'colaboradores/col-0007/certificado-medico.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0008',
                'salario' => 13000.00,
                'fecha_inicio' => '2023-07-01',
                'cargo_codigo' => 'CAR_CAMARERA',
                'departamento_codigo' => 'DEP_AMA_LLAVES',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0008/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0008/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0009',
                'salario' => 26000.00,
                'fecha_inicio' => '2023-07-01',
                'cargo_codigo' => 'CAR_MANT_JEFE',
                'departamento_codigo' => 'DEP_MANTENIMIENTO',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0009/identificacion.pdf'],
                    ['tipo' => 'expediente', 'archivo' => 'colaboradores/col-0009/expediente.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0010',
                'salario' => 30000.00,
                'fecha_inicio' => '2024-03-01',
                'cargo_codigo' => 'CAR_CHEF_EJEC',
                'departamento_codigo' => 'DEP_COCINA',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0010/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0010/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0011',
                'salario' => 14000.00,
                'fecha_inicio' => '2023-10-01',
                'cargo_codigo' => 'CAR_BARTENDER',
                'departamento_codigo' => 'DEP_RESTAURANTE',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0011/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0011/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0012',
                'salario' => 15000.00,
                'fecha_inicio' => '2022-09-01',
                'cargo_codigo' => 'CAR_SEGURIDAD',
                'departamento_codigo' => 'DEP_SEGURIDAD',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0012/identificacion.pdf'],
                    ['tipo' => 'certificado-medico', 'archivo' => 'colaboradores/col-0012/certificado-medico.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0013',
                'salario' => 22000.00,
                'fecha_inicio' => '2023-09-01',
                'cargo_codigo' => 'CAR_CONTADOR',
                'departamento_codigo' => 'DEP_CONTABILIDAD',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0013/identificacion.pdf'],
                    ['tipo' => 'contrato', 'archivo' => 'colaboradores/col-0013/contrato-laboral.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0014',
                'salario' => 16500.00,
                'fecha_inicio' => '2020-04-20',
                'cargo_codigo' => 'CAR_COMPRAS',
                'departamento_codigo' => 'DEP_COMPRAS',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0014/identificacion.pdf'],
                    ['tipo' => 'expediente', 'archivo' => 'colaboradores/col-0014/expediente.pdf'],
                ],
            ],
            [
                'codigo' => 'COL-0015',
                'salario' => 15500.00,
                'fecha_inicio' => '2021-01-11',
                'cargo_codigo' => 'CAR_MANT_TEC',
                'departamento_codigo' => 'DEP_MANTENIMIENTO',
                'documentos' => [
                    ['tipo' => 'identificacion', 'archivo' => 'colaboradores/col-0015/identificacion.pdf'],
                    ['tipo' => 'certificado-medico', 'archivo' => 'colaboradores/col-0015/certificado-medico.pdf'],
                ],
            ],
        ];
    }

    private function colaboradorId(string $codigo): ?int
    {
        $colaboradorId = DB::table('colaboradores')
            ->where('codigo', $codigo)
            ->value('id');

        return is_numeric($colaboradorId) ? (int) $colaboradorId : null;
    }

    private function texto(mixed $valor): string
    {
        return is_string($valor) ? $valor : '';
    }

    private function catalogoId(string $codigo): int
    {
        $catalogoId = DB::table('catalogos')
            ->where('codigo', $codigo)
            ->value('id');

        if (! is_numeric($catalogoId)) {
            throw new \RuntimeException("No se encontro el catalogo requerido: {$codigo}");
        }

        return (int) $catalogoId;
    }
}

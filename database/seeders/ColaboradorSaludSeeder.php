<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColaboradorSaludSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->registros() as $registro) {
            $codigoVal = $registro['codigo'] ?? '';
            $codigo = is_string($codigoVal) ? $codigoVal : '';
            $colaboradorId = $this->colaboradorId($codigo);

            if ($colaboradorId === null) {
                continue;
            }

            DB::table('colaborador_datos_medicos')->updateOrInsert(
                ['colaborador_id' => $colaboradorId],
                [
                    'tipo_sangre' => $registro['tipo_sangre'] ?? '',
                    'alergias' => $registro['alergias'] ?? '',
                    'enfermedades_cronicas' => $registro['enfermedades_cronicas'] ?? '',
                    'estado' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $contactos = $registro['contactos'] ?? [];
            if (is_array($contactos)) {
                foreach ($contactos as $contacto) {
                    if (is_array($contacto)) {
                        $telVal = $contacto['telefono'] ?? '';
                        $tel = is_string($telVal) ? $telVal : '';

                        $nomVal = $contacto['nombre'] ?? '';
                        $nom = is_string($nomVal) ? $nomVal : '';

                        $parentescoVal = $contacto['parentesco'] ?? '';
                        $parentesco = is_string($parentescoVal) ? $parentescoVal : '';

                        DB::table('colaborador_contactos_emergencia')->updateOrInsert(
                            [
                                'colaborador_id' => $colaboradorId,
                                'telefono' => $tel,
                            ],
                            [
                                'nombre' => $nom,
                                'parentesco' => $parentesco,
                                'estado' => 1,
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
    private function registros(): array
    {
        return [
            [
                'codigo' => 'COL-0001',
                'tipo_sangre' => 'O+',
                'alergias' => 'Penicilina',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    [
                        'nombre' => 'Elena Lopez',
                        'telefono' => '555-300-4001',
                        'parentesco' => 'Esposa',
                    ],
                    [
                        'nombre' => 'Rosa Perez',
                        'telefono' => '555-300-4002',
                        'parentesco' => 'Madre',
                    ],
                ],
            ],
            [
                'codigo' => 'COL-0002',
                'tipo_sangre' => 'A-',
                'alergias' => 'Mariscos',
                'enfermedades_cronicas' => 'Migraña ocasional',
                'contactos' => [
                    [
                        'nombre' => 'Luis Torres',
                        'telefono' => '555-300-4003',
                        'parentesco' => 'Hermano',
                    ],
                ],
            ],
            [
                'codigo' => 'COL-0003',
                'tipo_sangre' => 'B+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Hipertension controlada',
                'contactos' => [
                    [
                        'nombre' => 'Sofia Mendez',
                        'telefono' => '555-300-4004',
                        'parentesco' => 'Hermana',
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

        return is_numeric($colaboradorId) ? (int) $colaboradorId : null;
    }
}

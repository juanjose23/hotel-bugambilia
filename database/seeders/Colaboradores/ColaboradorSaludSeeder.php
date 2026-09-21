<?php

declare(strict_types=1);

namespace Database\Seeders\Colaboradores;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ColaboradorSaludSeeder extends Seeder
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
                    ['nombre' => 'Elena López', 'telefono' => '+505 8700 4001', 'parentesco' => 'Esposa'],
                    ['nombre' => 'Rosa Pérez', 'telefono' => '+505 8700 4002', 'parentesco' => 'Madre'],
                ],
            ],
            [
                'codigo' => 'COL-0002',
                'tipo_sangre' => 'A-',
                'alergias' => 'Mariscos',
                'enfermedades_cronicas' => 'Migraña ocasional',
                'contactos' => [
                    ['nombre' => 'Luis Torres Díaz', 'telefono' => '+505 8700 4003', 'parentesco' => 'Hermano'],
                ],
            ],
            [
                'codigo' => 'COL-0003',
                'tipo_sangre' => 'B+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Hipertensión controlada',
                'contactos' => [
                    ['nombre' => 'Sofía Méndez Ruiz', 'telefono' => '+505 8700 4004', 'parentesco' => 'Hermana'],
                ],
            ],
            [
                'codigo' => 'COL-0004',
                'tipo_sangre' => 'O+',
                'alergias' => 'Polvo',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Lucía Silva', 'telefono' => '+505 8700 4005', 'parentesco' => 'Hija'],
                ],
            ],
            [
                'codigo' => 'COL-0005',
                'tipo_sangre' => 'A+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Carlos Herrera', 'telefono' => '+505 8700 4006', 'parentesco' => 'Padre'],
                ],
            ],
            [
                'codigo' => 'COL-0006',
                'tipo_sangre' => 'O-',
                'alergias' => 'Sulfas',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Alberto Morales', 'telefono' => '+505 8700 4007', 'parentesco' => 'Esposo'],
                ],
            ],
            [
                'codigo' => 'COL-0007',
                'tipo_sangre' => 'O+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Mario Gómez', 'telefono' => '+505 8700 4008', 'parentesco' => 'Hermano'],
                ],
            ],
            [
                'codigo' => 'COL-0008',
                'tipo_sangre' => 'B-',
                'alergias' => 'Aspirina',
                'enfermedades_cronicas' => 'Asma leve',
                'contactos' => [
                    ['nombre' => 'Esperanza Ríos', 'telefono' => '+505 8700 4009', 'parentesco' => 'Madre'],
                ],
            ],
            [
                'codigo' => 'COL-0009',
                'tipo_sangre' => 'AB+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Claudia Solano', 'telefono' => '+505 8700 4010', 'parentesco' => 'Esposa'],
                ],
            ],
            [
                'codigo' => 'COL-0010',
                'tipo_sangre' => 'O+',
                'alergias' => 'Nueces',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Patricia Castillo', 'telefono' => '+505 8700 4011', 'parentesco' => 'Esposa'],
                ],
            ],
            [
                'codigo' => 'COL-0011',
                'tipo_sangre' => 'A+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Esteban Rocha', 'telefono' => '+505 8700 4012', 'parentesco' => 'Hermano'],
                ],
            ],
            [
                'codigo' => 'COL-0012',
                'tipo_sangre' => 'O+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Mercedes Gutiérrez', 'telefono' => '+505 8700 4013', 'parentesco' => 'Madre'],
                ],
            ],
            [
                'codigo' => 'COL-0013',
                'tipo_sangre' => 'A+',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'María Santos', 'telefono' => '+505 8700 4014', 'parentesco' => 'Esposa'],
                ],
            ],
            [
                'codigo' => 'COL-0014',
                'tipo_sangre' => 'O-',
                'alergias' => 'Penicilina',
                'enfermedades_cronicas' => 'Diabetes controlada',
                'contactos' => [
                    ['nombre' => 'Pedro Bonilla', 'telefono' => '+505 8700 4015', 'parentesco' => 'Padre'],
                ],
            ],
            [
                'codigo' => 'COL-0015',
                'tipo_sangre' => 'B-',
                'alergias' => 'Ninguna',
                'enfermedades_cronicas' => 'Ninguna',
                'contactos' => [
                    ['nombre' => 'Lucía Urbina', 'telefono' => '+505 8700 4016', 'parentesco' => 'Madre'],
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

<?php

namespace Database\Seeders;

use App\Enums\Personas\Sexo;
use App\Enums\Personas\TipoIdentificacion;
use App\Models\Colaboradores\Colaborador;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColaboradorBaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->colaboradores() as $colaborador) {
            $this->guardarColaboradorBase($colaborador);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function colaboradores(): array
    {
        return [
            [
                'codigo' => 'COL-0001',
                'email' => 'juan.perez@hotelbugambilias.test',
                'primer_nombre' => 'Juan',
                'segundo_nombre' => 'Carlos',
                'primer_apellido' => 'Perez',
                'segundo_apellido' => 'Lopez',
                'pais_iso2' => 'MX',
                'telefono' => '555-100-2001',
                'direccion' => 'Av. Central 123, Ciudad de Mexico',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-220589-0001A',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1989-05-12',
                'nss' => '85123456789',
                'fecha_ingreso' => '2024-02-10',
                'imagen_url' => 'colaboradores/col-0001/perfil.jpg',
            ],
            [
                'codigo' => 'COL-0002',
                'email' => 'mariana.torres@hotelbugambilias.test',
                'primer_nombre' => 'Mariana',
                'segundo_nombre' => 'Elena',
                'primer_apellido' => 'Torres',
                'segundo_apellido' => 'Diaz',
                'pais_iso2' => 'CO',
                'telefono' => '555-100-2002',
                'direccion' => 'Calle 45 #18-30, Bogota',
                'tipo_identificacion' => TipoIdentificacion::Pasaporte->value,
                'numero_identificacion' => 'PA-884421',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1991-11-27',
                'nss' => '85123456790',
                'fecha_ingreso' => '2023-08-15',
                'imagen_url' => 'colaboradores/col-0002/perfil.jpg',
            ],
            [
                'codigo' => 'COL-0003',
                'email' => 'carlos.mendez@hotelbugambilias.test',
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => 'Andres',
                'primer_apellido' => 'Mendez',
                'segundo_apellido' => 'Ruiz',
                'pais_iso2' => 'GT',
                'telefono' => '555-100-2003',
                'direccion' => 'Zona 10, Guatemala',
                'tipo_identificacion' => TipoIdentificacion::Dni->value,
                'numero_identificacion' => 'DNI-5588991',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1987-02-03',
                'nss' => '85123456791',
                'fecha_ingreso' => '2022-01-20',
                'imagen_url' => 'colaboradores/col-0003/perfil.jpg',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function guardarColaboradorBase(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $paisIso2Val = $data['pais_iso2'] ?? '';
            $paisIso2 = is_string($paisIso2Val) ? $paisIso2Val : '';
            $paisId = $this->paisId($paisIso2);

            DB::table('personas')->updateOrInsert(
                [
                    'primer_nombre' => $data['primer_nombre'],
                    'segundo_nombre' => $data['segundo_nombre'],
                    'pais_id' => $paisId,
                    'tipo_persona' => 'natural',
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $personaId = DB::table('personas')
                ->where('primer_nombre', $data['primer_nombre'])
                ->where('segundo_nombre', $data['segundo_nombre'])
                ->where('telefono', $data['telefono'])
                ->where('direccion', $data['direccion'])
                ->value('id');

            if ($personaId === null) {
                return;
            }

            DB::table('personas_naturales')->updateOrInsert(
                ['persona_id' => $personaId],
                [
                    'primer_apellido' => $data['primer_apellido'],
                    'segundo_apellido' => $data['segundo_apellido'],
                    'tipo_identificacion' => $data['tipo_identificacion'],
                    'numero_identificacion' => $data['numero_identificacion'],
                    'sexo' => $data['sexo'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('colaboradores')->updateOrInsert(
                ['codigo' => $data['codigo']],
                [
                    'persona_id' => $personaId,
                    'nss' => $data['nss'],
                    'fecha_ingreso' => $data['fecha_ingreso'],
                    'estado' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $colaboradorId = DB::table('colaboradores')
                ->where('codigo', $data['codigo'])
                ->value('id');

            if ($colaboradorId === null) {
                return;
            }

            DB::table('imagenes')->updateOrInsert(
                [
                    'imagenable_type' => Colaborador::class,
                    'imagenable_id' => $colaboradorId,
                ],
                [
                    'url' => $data['imagen_url'],
                    'public_id' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });
    }

    private function paisId(string $codigoIso2): ?int
    {
        $paisId = DB::table('paises')
            ->where('codigo_iso2', $codigoIso2)
            ->value('id');

        if (is_numeric($paisId)) {
            return (int) $paisId;
        }

        $fallback = DB::table('paises')->orderBy('id')->value('id');

        return is_numeric($fallback) ? (int) $fallback : null;
    }
}

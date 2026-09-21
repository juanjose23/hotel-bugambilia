<?php

declare(strict_types=1);

namespace Database\Seeders\Colaboradores;

use App\Enums\Personas\Sexo;
use App\Enums\Personas\TipoIdentificacion;
use App\Repository\Models\Colaboradores\Colaborador;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class ColaboradorBaseSeeder extends Seeder
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
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2001',
                'direccion' => 'Colonia Centroamérica #12, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-120589-0001A',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1989-05-12',
                'nss' => '85123456789',
                'fecha_ingreso' => '2023-01-15',
                'imagen_url' => 'colaboradores/col-0001/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['recepcionista', 'recepcion_encargado'],
            ],
            [
                'codigo' => 'COL-0002',
                'email' => 'mariana.torres@hotelbugambilias.test',
                'primer_nombre' => 'Mariana',
                'segundo_nombre' => 'Elena',
                'primer_apellido' => 'Torres',
                'segundo_apellido' => 'Diaz',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2002',
                'direccion' => 'Residencial Los Robles #45, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-271191-0002B',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1991-11-27',
                'nss' => '85123456790',
                'fecha_ingreso' => '2022-08-01',
                'imagen_url' => 'colaboradores/col-0002/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['gerente', 'administrador', 'compras_aprobador', 'inventario_encargado', 'activos_encargado'],
            ],
            [
                'codigo' => 'COL-0003',
                'email' => 'carlos.mendez@hotelbugambilias.test',
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => 'Andres',
                'primer_apellido' => 'Mendez',
                'segundo_apellido' => 'Ruiz',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2003',
                'direccion' => 'Villa Fontana Sur #88, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-030287-0003C',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1987-02-03',
                'nss' => '85123456791',
                'fecha_ingreso' => '2021-03-10',
                'imagen_url' => 'colaboradores/col-0003/perfil.jpg',
                'con_usuario' => true,
                'rol' => 'super_admin',
            ],
            [
                'codigo' => 'COL-0004',
                'email' => 'roberto.silva@hotelbugambilias.test',
                'primer_nombre' => 'Roberto',
                'segundo_nombre' => 'Antonio',
                'primer_apellido' => 'Silva',
                'segundo_apellido' => 'Mendieta',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2004',
                'direccion' => 'Reparto San Juan #104, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-140885-0004D',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1985-08-14',
                'nss' => '85123456792',
                'fecha_ingreso' => '2022-02-15',
                'imagen_url' => 'colaboradores/col-0004/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['recepcionista', 'recepcion_supervisor'],
            ],
            [
                'codigo' => 'COL-0005',
                'email' => 'sofia.herrera@hotelbugambilias.test',
                'primer_nombre' => 'Sofia',
                'segundo_nombre' => 'Valentina',
                'primer_apellido' => 'Herrera',
                'segundo_apellido' => 'Cruz',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2005',
                'direccion' => 'Bello Horizonte C-22, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-190495-0005E',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1995-04-19',
                'nss' => '85123456793',
                'fecha_ingreso' => '2024-01-10',
                'imagen_url' => 'colaboradores/col-0005/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['recepcionista'],
            ],
            [
                'codigo' => 'COL-0006',
                'email' => 'marta.morales@hotelbugambilias.test',
                'primer_nombre' => 'Marta',
                'segundo_nombre' => 'Isabel',
                'primer_apellido' => 'Morales',
                'segundo_apellido' => 'Fonseca',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2006',
                'direccion' => 'Colonia Nicarao E-14, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-081080-0006F',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1980-10-08',
                'nss' => '85123456794',
                'fecha_ingreso' => '2021-06-01',
                'imagen_url' => 'colaboradores/col-0006/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['ama_llaves', 'limpieza_supervisor', 'limpieza_encargado', 'admin'],
            ],
            [
                'codigo' => 'COL-0007',
                'email' => 'rosa.gomez@hotelbugambilias.test',
                'primer_nombre' => 'Rosa',
                'segundo_nombre' => 'Amanda',
                'primer_apellido' => 'Gomez',
                'segundo_apellido' => 'Narvaez',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2007',
                'direccion' => 'Barrio Riguero #55, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-220392-0007G',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1992-03-22',
                'nss' => '85123456795',
                'fecha_ingreso' => '2023-04-15',
                'imagen_url' => 'colaboradores/col-0007/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['ama_llaves'],
            ],
            [
                'codigo' => 'COL-0008',
                'email' => 'carmen.alvarado@hotelbugambilias.test',
                'primer_nombre' => 'Carmen',
                'segundo_nombre' => 'Lucia',
                'primer_apellido' => 'Alvarado',
                'segundo_apellido' => 'Rios',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2008',
                'direccion' => 'Monseñor Lezcano #78, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-150993-0008H',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1993-09-15',
                'nss' => '85123456796',
                'fecha_ingreso' => '2023-07-01',
                'imagen_url' => 'colaboradores/col-0008/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['ama_llaves'],
            ],
            [
                'codigo' => 'COL-0009',
                'email' => 'francisco.bermudez@hotelbugambilias.test',
                'primer_nombre' => 'Francisco',
                'segundo_nombre' => 'Javier',
                'primer_apellido' => 'Bermudez',
                'segundo_apellido' => 'Solano',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2009',
                'direccion' => 'Carretera a Masaya Km 10.5, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-050682-0009I',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1982-06-05',
                'nss' => '85123456797',
                'fecha_ingreso' => '2021-11-15',
                'imagen_url' => 'colaboradores/col-0009/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['mantenimiento'],
            ],
            [
                'codigo' => 'COL-0010',
                'email' => 'pedro.castillo@hotelbugambilias.test',
                'primer_nombre' => 'Pedro',
                'segundo_nombre' => 'Jose',
                'primer_apellido' => 'Castillo',
                'segundo_apellido' => 'Rivas',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2010',
                'direccion' => 'Las Colinas #120, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-300186-0010J',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1986-01-30',
                'nss' => '85123456798',
                'fecha_ingreso' => '2022-05-20',
                'imagen_url' => 'colaboradores/col-0010/perfil.jpg',
                'con_usuario' => true,
                'roles' => ['restaurante', 'restaurante_encargado', 'restaurante_cocina'],
            ],
            [
                'codigo' => 'COL-0011',
                'email' => 'david.rocha@hotelbugambilias.test',
                'primer_nombre' => 'David',
                'segundo_nombre' => 'Alejandro',
                'primer_apellido' => 'Rocha',
                'segundo_apellido' => 'Mendoza',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2011',
                'direccion' => 'Linda Vista Sur #44, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-110796-0011K',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1996-07-11',
                'nss' => '85123456799',
                'fecha_ingreso' => '2023-10-01',
                'imagen_url' => 'colaboradores/col-0011/perfil.jpg',
                'con_usuario' => false,
            ],
            [
                'codigo' => 'COL-0012',
                'email' => 'jorge.estrada@hotelbugambilias.test',
                'primer_nombre' => 'Jorge',
                'segundo_nombre' => 'Luis',
                'primer_apellido' => 'Estrada',
                'segundo_apellido' => 'Gutierrez',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2012',
                'direccion' => 'Ciudad Sandino Z-4, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-251283-0012L',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1983-12-25',
                'nss' => '85123456800',
                'fecha_ingreso' => '2022-09-01',
                'imagen_url' => 'colaboradores/col-0012/perfil.jpg',
                'con_usuario' => false,
            ],
            [
                'codigo' => 'COL-0013',
                'email' => 'jose.maradiaga@hotelbugambilias.test',
                'primer_nombre' => 'Jose',
                'segundo_nombre' => 'Miguel',
                'primer_apellido' => 'Maradiaga',
                'segundo_apellido' => 'Santos',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2013',
                'direccion' => 'Reparto Bolonia Km 4.5, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-070976-0013M',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1976-09-07',
                'nss' => '85123456801',
                'fecha_ingreso' => '2021-09-01',
                'imagen_url' => 'colaboradores/col-0013/perfil.jpg',
                'con_usuario' => false,
            ],
            [
                'codigo' => 'COL-0014',
                'email' => 'luisa.bonilla@hotelbugambilias.test',
                'primer_nombre' => 'Luisa',
                'segundo_nombre' => 'Fernanda',
                'primer_apellido' => 'Bonilla',
                'segundo_apellido' => 'Ubeda',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2014',
                'direccion' => 'Colonia 10 de Junio #33, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-140289-0014N',
                'sexo' => Sexo::FEMENINO->value,
                'fecha_nacimiento' => '1989-02-14',
                'nss' => '85123456802',
                'fecha_ingreso' => '2020-04-20',
                'imagen_url' => 'colaboradores/col-0014/perfil.jpg',
                'con_usuario' => false,
            ],
            [
                'codigo' => 'COL-0015',
                'email' => 'sergio.urbina@hotelbugambilias.test',
                'primer_nombre' => 'Sergio',
                'segundo_nombre' => 'Antonio',
                'primer_apellido' => 'Urbina',
                'segundo_apellido' => 'Mairena',
                'pais_iso2' => 'NI',
                'telefono' => '+505 8888 2015',
                'direccion' => 'Villa Rubén Darío #67, Managua',
                'tipo_identificacion' => TipoIdentificacion::Cedula->value,
                'numero_identificacion' => '001-230185-0015O',
                'sexo' => Sexo::MASCULINO->value,
                'fecha_nacimiento' => '1985-01-23',
                'nss' => '85123456803',
                'fecha_ingreso' => '2021-01-11',
                'imagen_url' => 'colaboradores/col-0015/perfil.jpg',
                'con_usuario' => false,
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

            if (! is_numeric($personaId)) {
                return;
            }
            $personaId = (int) $personaId;

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

            if (! is_numeric($colaboradorId)) {
                return;
            }

            DB::table('imagenes')->updateOrInsert(
                [
                    'imagenable_type' => Colaborador::class,
                    'imagenable_id' => (int) $colaboradorId,
                ],
                [
                    'url' => $data['imagen_url'],
                    'public_id' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $conUsuarioVal = $data['con_usuario'] ?? false;
            if (is_bool($conUsuarioVal) && $conUsuarioVal) {
                $this->crearUsuario($data, $personaId);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function crearUsuario(array $data, int $personaId): void
    {
        $emailVal = $data['email'] ?? '';
        $email = is_string($emailVal) ? $emailVal : '';

        $primerNombre = is_string($data['primer_nombre']) ? $data['primer_nombre'] : '';

        $apellidoVal = $data['primer_apellido'] ?? '';
        $primerApellido = is_string($apellidoVal) ? $apellidoVal : '';
        $name = trim($primerNombre.' '.$primerApellido);

        $roles = $this->rolesDe($data);

        /** @var User $user */
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'persona_id' => $personaId,
                'name' => $name !== '' ? $name : 'Colaborador',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        try {
            if (class_exists(Role::class)) {
                foreach ($roles as $rolName) {
                    $role = Role::firstOrCreate(['name' => $rolName, 'guard_name' => 'web']);
                    if (! $user->hasRole($rolName)) {
                        $user->assignRole($role);
                    }
                }
            }
        } catch (\Throwable) {
            // Si aún no están listas las tablas de roles, continuar
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, string>
     */
    private function rolesDe(array $data): array
    {
        $rolesVal = $data['roles'] ?? [];
        $roles = is_array($rolesVal) ? $rolesVal : [];

        if ($roles === []) {
            $rolVal = $data['rol'] ?? 'recepcionista';
            $rol = is_string($rolVal) ? $rolVal : 'recepcionista';
            $roles = [$rol];
        }

        return array_values(array_filter(
            array_map(
                static fn (mixed $rol): string => is_string($rol) ? $rol : '',
                $roles,
            ),
            static fn (string $rol): bool => $rol !== ''
        ));
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

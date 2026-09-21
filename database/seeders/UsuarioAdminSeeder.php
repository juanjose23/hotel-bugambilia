<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $paisNicaragua = DB::table('paises')->where('codigo_iso2', 'NI')->first();
        $paisId = $paisNicaragua->id ?? DB::table('paises')->value('id') ?? 1;

        // 1. Super Administrador General
        $this->crearUsuarioAdmin(
            email: 'admin@hotel.com',
            password: 'password',
            nombreCompleto: 'Administrador General',
            primerNombre: 'Carlos',
            segundoNombre: 'Alberto',
            primerApellido: 'Mendoza',
            segundoApellido: 'Ruiz',
            telefono: '+505 8888 0001',
            direccion: 'Hotel Bugambilias, Recepción Principal',
            paisId: (int) $paisId,
            isAdmin: true,
            roleName: 'super_admin'
        );

        // 2. Gerente de Operaciones
        $this->crearUsuarioAdmin(
            email: 'gerente@hotel.com',
            password: 'password',
            nombreCompleto: 'Mariana Torres',
            primerNombre: 'Mariana',
            segundoNombre: 'Elena',
            primerApellido: 'Torres',
            segundoApellido: 'Díaz',
            telefono: '+505 8888 0002',
            direccion: 'Residencial Los Robles #45, Managua',
            paisId: (int) $paisId,
            isAdmin: true,
            roleName: 'gerente'
        );

        // 3. Recepcionista Principal (Front Desk)
        $this->crearUsuarioAdmin(
            email: 'recepcion@hotel.com',
            password: 'password',
            nombreCompleto: 'Juan Pérez López',
            primerNombre: 'Juan',
            segundoNombre: 'Carlos',
            primerApellido: 'Pérez',
            segundoApellido: 'López',
            telefono: '+505 8888 0003',
            direccion: 'Colonia Centroamérica #12, Managua',
            paisId: (int) $paisId,
            isAdmin: true,
            roleName: 'recepcionista'
        );

        $this->command->info('Usuarios administrativos creados/verificados: admin@hotel.com, gerente@hotel.com, recepcion@hotel.com (Contraseña: password)');
    }

    private function crearUsuarioAdmin(
        string $email,
        string $password,
        string $nombreCompleto,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
        string $telefono,
        string $direccion,
        int $paisId,
        bool $isAdmin,
        string $roleName
    ): User {
        return DB::transaction(function () use (
            $email,
            $password,
            $nombreCompleto,
            $primerNombre,
            $segundoNombre,
            $primerApellido,
            $segundoApellido,
            $telefono,
            $direccion,
            $paisId,
            $isAdmin,
            $roleName
        ): User {
            // 1. Persona Base
            $persona = Persona::firstOrCreate(
                [
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'telefono' => $telefono,
                ],
                [
                    'pais_id' => $paisId,
                    'tipo_persona' => 'natural',
                    'direccion' => $direccion,
                ]
            );

            // 2. Persona Natural
            PersonaNatural::firstOrCreate(
                ['persona_id' => $persona->id],
                [
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,
                    'tipo_identificacion' => 'cedula',
                    'numero_identificacion' => '001-'.rand(100000, 999999).'-0001A',
                    'sexo' => 'M',
                    'fecha_nacimiento' => '1988-06-15',
                ]
            );

            // 3. Usuario del Sistema
            /** @var User $user */
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'persona_id' => $persona->id,
                    'name' => $nombreCompleto,
                    'password' => Hash::make($password),
                    'is_admin' => $isAdmin,
                    'email_verified_at' => now(),
                ]
            );

            // 4. Asignar rol si Spatie Permission está disponible
            try {
                if (class_exists(Role::class)) {
                    $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                    if (! $user->hasRole($roleName)) {
                        $user->assignRole($role);
                    }
                }
            } catch (\Throwable) {
                // Si aún no están listas las tablas de roles, continuar
            }

            return $user;
        });
    }
}

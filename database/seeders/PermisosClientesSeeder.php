<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisosClientesSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'Usuarios:VerClientes',
            'Usuarios:CrearClientes',
            'Usuarios:EditarClientes',
            'Usuarios:VerConflictosIdentidad',
            'Usuarios:ResolverConflictosIdentidad',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'admin')->first();

        if ($admin !== null) {
            $admin->syncPermissions(
                $admin->permissions->pluck('name')->merge($permisos)->unique()->toArray()
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders\Usuarios;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class RolAdministracionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->roles() as $nombre) {
            if ($nombre === '') {
                continue;
            }

            Role::firstOrCreate([
                'name' => $nombre,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Roles de administración agrupados por rubro del hotel.
     *
     * @return array<int, string>
     */
    private function roles(): array
    {
        return [
            // Núcleo / Dirección
            'admin',
            'administrador',
            'gerente',
            'panel_user',
            'super_admin',

            // Recepción
            'recepcionista',
            'recepcion_encargado',
            'recepcion_supervisor',

            // Limpieza & Ama de llaves
            'ama_llaves',
            'limpieza_encargado',
            'limpieza_supervisor',

            // Mantenimiento
            'mantenimiento',

            // Restaurante
            'restaurante',
            'restaurante_encargado',
            'restaurante_cocina',
            'restaurante_mesero',

            // Compras
            'compras_encargado',
            'compras_aprobador',

            // Inventario
            'inventario_encargado',
            'inventario_responsable',

            // Activos fijos
            'activos_encargado',
            'activos_responsable',

            // Rubros adicionales
            'seguridad',
            'contabilidad',
        ];
    }
}

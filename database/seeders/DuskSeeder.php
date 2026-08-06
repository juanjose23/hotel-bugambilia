<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Seeder exclusivo para las pruebas E2E de Dusk.
 *
 * Construye un escenario determinista del módulo Restaurante:
 * monedas, catálogos, menú, espacio del restaurante y mesas hijas,
 * además del usuario administrador con rol super_admin.
 *
 * No incluye pedidos ni reservas demo para no interferir con el E2E.
 */
final class DuskSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Datos base
        $this->call(MonedaSeeder::class);
        $this->call(CatalogoTipoSeeder::class);
        $this->call(CatalogoSeeder::class);
        $this->call(UbicacionSeeder::class);

        // 2. Menú del restaurante (categorías, platos, recetas y stock en cocina)
        $this->call(MenuRestauranteSeeder::class);

        // 3. Espacio principal del restaurante y mesas hijas
        $espacio = Espacio::updateOrCreate(
            ['codigo' => 'REST-001'],
            [
                'nombre' => 'Restaurante Bugambilias',
                'tipo' => TipoEspacio::RESTAURANTE,
                'estado' => EstadoEspacio::Disponible,
                'capacidad_personas' => 50,
                'web' => true,
                'reservable' => true,
            ]
        );

        $mesas = [
            ['codigo' => 'MESA-01', 'nombre' => 'Mesa 01', 'capacidad' => 2, 'zona' => 'interior', 'tipo_mesa' => '2_personas'],
            ['codigo' => 'MESA-02', 'nombre' => 'Mesa 02', 'capacidad' => 2, 'zona' => 'interior', 'tipo_mesa' => '2_personas'],
            ['codigo' => 'MESA-03', 'nombre' => 'Mesa 03', 'capacidad' => 4, 'zona' => 'interior', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-04', 'nombre' => 'Mesa 04', 'capacidad' => 4, 'zona' => 'interior', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-05', 'nombre' => 'Mesa 05', 'capacidad' => 6, 'zona' => 'interior', 'tipo_mesa' => '6_personas'],
            ['codigo' => 'MESA-06', 'nombre' => 'Mesa 06', 'capacidad' => 4, 'zona' => 'terraza', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-07', 'nombre' => 'Mesa 07 (VIP)', 'capacidad' => 4, 'zona' => 'terraza', 'tipo_mesa' => 'VIP'],
            ['codigo' => 'MESA-08', 'nombre' => 'Mesa 08', 'capacidad' => 2, 'zona' => 'terraza', 'tipo_mesa' => '2_personas'],
            ['codigo' => 'BAR-01', 'nombre' => 'Barra 01', 'capacidad' => 1, 'zona' => 'bar', 'tipo_mesa' => 'barra'],
            ['codigo' => 'BAR-02', 'nombre' => 'Barra 02', 'capacidad' => 1, 'zona' => 'bar', 'tipo_mesa' => 'barra'],
        ];

        foreach ($mesas as $index => $data) {
            Espacio::updateOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'nombre' => $data['nombre'],
                    'padre_id' => $espacio->id,
                    'tipo' => TipoEspacio::MESA,
                    'capacidad_personas' => $data['capacidad'],
                    'estado' => EstadoEspacio::Disponible,
                    'web' => true,
                    'reservable' => true,
                    'orden' => $index + 1,
                    'meta_datos' => [
                        'capacidad_personas' => $data['capacidad'],
                        'tipo_mesa' => $data['tipo_mesa'],
                        'zona_restaurante' => $data['zona'],
                    ],
                ]
            );
        }

        // 4. Usuario administrador para el login del navegador
        $admin = User::updateOrCreate(
            ['email' => 'admin@bugambilia.test'],
            [
                'name' => 'Administrador Dusk',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'password_change_required' => false,
            ]
        );

        $rol = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name', 'super_admin'),
            'guard_name' => 'web',
        ]);
        $admin->assignRole($rol);
    }
}

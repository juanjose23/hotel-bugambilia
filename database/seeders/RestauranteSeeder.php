<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Espacios\Espacio;
use Illuminate\Database\Seeder;

class RestauranteSeeder extends Seeder
{
    public function run(): void
    {
        $espacio = Espacio::where('codigo', 'REST-001')->firstOrFail();
        // ─── Mesas ───
        $mesas = [
            ['codigo' => 'MESA-01', 'nombre' => 'Mesa 01', 'capacidad' => 2, 'zona' => 'interior', 'tipo_mesa' => '2_personas'],
            ['codigo' => 'MESA-02', 'nombre' => 'Mesa 02', 'capacidad' => 2, 'zona' => 'interior', 'tipo_mesa' => '2_personas'],
            ['codigo' => 'MESA-03', 'nombre' => 'Mesa 03', 'capacidad' => 4, 'zona' => 'interior', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-04', 'nombre' => 'Mesa 04', 'capacidad' => 4, 'zona' => 'interior', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-05', 'nombre' => 'Mesa 05', 'capacidad' => 6, 'zona' => 'interior', 'tipo_mesa' => '6_personas'],
            ['codigo' => 'MESA-06', 'nombre' => 'Mesa 06', 'capacidad' => 4, 'zona' => 'terraza', 'tipo_mesa' => '4_personas'],
            ['codigo' => 'MESA-07', 'nombre' => 'Mesa 07', 'capacidad' => 4, 'zona' => 'terraza', 'tipo_mesa' => 'VIP'],
            ['codigo' => 'MESA-08', 'nombre' => 'Mesa 08', 'capacidad' => 2, 'zona' => 'terraza', 'tipo_mesa' => '2_personas'],
        ];

        foreach ($mesas as $data) {
            Espacio::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'nombre' => $data['nombre'],
                    'padre_id' => $espacio->id,
                    'tipo' => 'mesa',
                    'capacidad_personas' => $data['capacidad'],
                    'estado' => 1,
                    'meta_datos' => json_encode([
                        'tipo_mesa' => $data['tipo_mesa'],
                        'zona_restaurante' => $data['zona'],
                    ]),
                ]
            );
        }

        $this->command->info('Restaurante Bugambilias: espacio, ambientes y mesas creados.');
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Database\Seeder;

final class RestauranteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear / Verificar Espacio Principal del Restaurante
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

        // 2. Mesas del Restaurante (Interior, Terraza, VIP, Barra)
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

        // 3. Cargar Menú (Categorías, Platillos, Recetas e Insumos)
        $this->call(MenuRestauranteSeeder::class);

        // 4. Cargar Reservaciones futuras de Mesas y Espacios.
        // Las reservas bloquean por fecha/hora en reserva_detalles, no dejan la mesa ocupada permanentemente.
        $this->call(ReservasRestoMesSeeder::class);

        // 5. Cargar Pedidos Demo Activos e Históricos usando el flujo operativo de restaurante.
        $this->call(PedidoRestauranteSeeder::class);

        $this->command->info('Restaurante Bugambilias: Espacio, Mesas, Menú, Pedidos y Reservas creados exitosamente en un solo Seeder unificado.');
    }
}

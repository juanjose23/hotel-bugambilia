<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use Illuminate\Database\Seeder;

class RestauranteSeeder extends Seeder
{
    public function run(): void
    {
        // Crear ubicación Cocina Restaurante
        Ubicacion::firstOrCreate(
            ['nombre' => 'Cocina Restaurante'],
            ['tipo' => 'bodega', 'descripcion' => 'Stock de ingredientes de cocina', 'estado' => 1, 'orden' => 50]
        );

        // Crear categoría Restaurante si no existe
        $categoriaRest = Catalogo::firstOrCreate(
            ['codigo' => 'RESTAURANTE', 'catalogo_tipo_id' => Catalogo::whereHas('catalogoTipo', fn ($q) => $q->where('codigo', 'CATEGORIA_SERVICIO'))->first()?->catalogo_tipo_id ?: 8],
            ['nombre' => 'Restaurante', 'estado' => 1]
        );

        // ─── Espacio Restaurante ───
        $restaurante = Espacio::firstOrCreate(
            ['codigo' => 'REST-0001'],
            [
                'nombre' => 'Restaurante Bugambilias',
                'tipo' => 'restaurante',
                'capacidad_personas' => 40,
                'estado' => 1,
                'meta_datos' => json_encode([
                    'tipo_cocina' => 'Nicaragüense / Internacional',
                    'tipo_servicio' => 'A la carta',
                    'horario_desayuno' => '07:00 - 10:00',
                    'horario_almuerzo' => '12:00 - 15:00',
                    'horario_cena' => '18:00 - 22:00',
                ]),
            ]
        );

        // ─── Ambientes del Restaurante ───
        $ambientes = [
            [
                'codigo' => 'AMB-SALON',
                'nombre' => 'Salón Principal Bugambilias',
                'tipo' => 'ambiente',
                'capacidad' => 25,
                'descripcion' => 'Salón climatizado de ambiente sobrio y elegante, ideal para almuerzos ejecutivos y cenar en total serenidad.',
                'meta' => [
                    'zona_restaurante' => 'interior',
                    'caracteristicas' => ['Aire Acondicionado', 'Música de Fondo', 'Iluminación Cálida', 'Vista a la Galería'],
                ],
            ],
            [
                'codigo' => 'AMB-TERRAZA',
                'nombre' => 'Terraza al Aire Libre',
                'tipo' => 'terraza',
                'capacidad' => 20,
                'descripcion' => 'Terraza exterior rodeada de flora tropical y bugambilias en flor. Disfrute del clima fresco de Estelí.',
                'meta' => [
                    'zona_restaurante' => 'terraza',
                    'caracteristicas' => ['Vista al Jardín', 'Pérgola Iluminada', 'Brisa Natural', 'Mesas al Aire Libre'],
                ],
            ],
            [
                'codigo' => 'AMB-BAR',
                'nombre' => 'Bar & Lounge El Mirador',
                'tipo' => 'bar',
                'capacidad' => 15,
                'descripcion' => 'Espacio moderno para degustar coctelería de autor, licores seleccionados y botanas gourmets.',
                'meta' => [
                    'zona_restaurante' => 'barra',
                    'caracteristicas' => ['Barra de Cocteles', 'Pantalla HD', 'Música Ambient', 'Asientos Lounge'],
                ],
            ],
            [
                'codigo' => 'AMB-VIP',
                'nombre' => 'Cenador Privado VIP',
                'tipo' => 'ambiente',
                'capacidad' => 10,
                'descripcion' => 'Reservado VIP rodeado de vegetación para celebraciones familiares, aniversarios o reuniones de negocios.',
                'meta' => [
                    'zona_restaurante' => 'vip',
                    'caracteristicas' => ['Servicio Exclusivo', 'Atención Dedicada', 'Ambiente Privado', 'Decoración Especial'],
                ],
            ],
        ];

        foreach ($ambientes as $amb) {
            Espacio::firstOrCreate(
                ['codigo' => $amb['codigo']],
                [
                    'nombre' => $amb['nombre'],
                    'padre_id' => $restaurante->id,
                    'tipo' => $amb['tipo'],
                    'capacidad_personas' => $amb['capacidad'],
                    'descripcion' => $amb['descripcion'],
                    'estado' => 1,
                    'meta_datos' => json_encode($amb['meta']),
                ]
            );
        }

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
                    'padre_id' => $restaurante->id,
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

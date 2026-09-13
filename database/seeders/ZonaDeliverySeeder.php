<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\BusinessLogic\Personas\PersonaNatural\CatalogoMunicipiosNicaragua;
use App\Repository\Models\Restaurante\ZonaDelivery;
use Illuminate\Database\Seeder;

final class ZonaDeliverySeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = CatalogoMunicipiosNicaragua::obtenerCatalogo();

        // Mapeo de configuraciones por defecto
        $configuraciones = [
            'Estelí' => ['codigo' => 'EST', 'activo' => true, 'costo_envio' => 50.00, 'orden' => 1],
            'Madriz' => ['codigo' => 'MAD', 'activo' => false, 'costo_envio' => 120.00, 'orden' => 2],
            'Nueva Segovia' => ['codigo' => 'NVA', 'activo' => false, 'costo_envio' => 150.00, 'orden' => 3],
            'Managua' => ['codigo' => 'MGA', 'activo' => false, 'costo_envio' => 250.00, 'orden' => 4],
            'Matagalpa' => ['codigo' => 'MAT', 'activo' => false, 'costo_envio' => 180.00, 'orden' => 5],
            'Jinotega' => ['codigo' => 'JIN', 'activo' => false, 'costo_envio' => 180.00, 'orden' => 6],
            'León' => ['codigo' => 'LEO', 'activo' => false, 'costo_envio' => 200.00, 'orden' => 7],
            'Chinandega' => ['codigo' => 'CHN', 'activo' => false, 'costo_envio' => 220.00, 'orden' => 8],
            'Masaya' => ['codigo' => 'MAS', 'activo' => false, 'costo_envio' => 260.00, 'orden' => 9],
            'Carazo' => ['codigo' => 'CAR', 'activo' => false, 'costo_envio' => 270.00, 'orden' => 10],
            'Granada' => ['codigo' => 'GRA', 'activo' => false, 'costo_envio' => 270.00, 'orden' => 11],
            'Rivas' => ['codigo' => 'RIV', 'activo' => false, 'costo_envio' => 300.00, 'orden' => 12],
            'Boaco' => ['codigo' => 'BOA', 'activo' => false, 'costo_envio' => 220.00, 'orden' => 13],
            'Chontales' => ['codigo' => 'CHO', 'activo' => false, 'costo_envio' => 250.00, 'orden' => 14],
            'Río San Juan' => ['codigo' => 'RSJ', 'activo' => false, 'costo_envio' => 350.00, 'orden' => 15],
            'Costa Caribe Norte' => ['codigo' => 'CCN', 'activo' => false, 'costo_envio' => 450.00, 'orden' => 16],
            'Costa Caribe Sur' => ['codigo' => 'CCS', 'activo' => false, 'costo_envio' => 450.00, 'orden' => 17],
        ];

        $ordenIndex = 1;
        foreach ($catalogo as $item) {
            $nombreDep = $item['departamento'];
            $config = $configuraciones[$nombreDep] ?? [
                'codigo' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nombreDep) ?? 'DEP', 0, 3)),
                'activo' => false,
                'costo_envio' => 200.00,
                'orden' => $ordenIndex,
            ];

            $municipiosNombres = array_map(
                fn (array $m): string => $m['nombre'],
                $item['municipios']
            );

            ZonaDelivery::updateOrCreate(
                ['codigo' => $config['codigo']],
                [
                    'nombre' => $nombreDep,
                    'activo' => $config['activo'],
                    'costo_envio' => $config['costo_envio'],
                    'orden' => $config['orden'],
                    'municipios' => $municipiosNombres,
                ]
            );

            $ordenIndex++;
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $tipos = [

            [
                'codigo' => 'CATEGORIA_HAB',
                'nombre' => 'Categoría de habitación',
                'estado' => 1
            ],
            [
                'codigo' => 'CAPACIDAD_HAB',
                'nombre' => 'Capacidad de habitación',
                'estado' => 1
            ],
            [
                'codigo' => 'TIPO_TARIFA',
                'nombre' => 'Tipo de tarifa',
                'estado' => 1
            ],
            [
                'codigo' => 'AMENIDAD_HAB',
                'nombre' => 'Amenidad de habitación',
                'estado' => 1
            ],

            // Colaboradores
            [
                'codigo' => 'CARGO',
                'nombre' => 'Cargo de colaborador',
                'estado' => 1
            ],
            [
                'codigo' => 'DEPARTAMENTO',
                'nombre' => 'Departamento',
                'estado' => 1
            ],

            // Clientes
            [
                'codigo' => 'TIPO_CLIENTE',
                'nombre' => 'Tipo de cliente',
                'estado' => 1
            ],

            [
                'codigo' => 'SECTOR_COMERCIAL',
                'nombre' => 'Sector comercial',
                'estado' => 1
            ],
            //Inventario
            [
                'codigo' => 'TIPO_MOVIMIENTO_INV',
                'nombre' => 'Tipo de movimiento de inventario',
                'estado' => 1
            ],

            // Servicios
            [
                'codigo' => 'CATEGORIA_SERVICIO',
                'nombre' => 'Categoría de servicio',
                'estado' => 1
            ],
            [
                'codigo' => 'TIPO_SERVICIO',
                'nombre' => 'Tipo de servicio',
                'estado' => 1
            ],

            // Promociones
            [
                'codigo' => 'TIPO_PROMOCION',
                'nombre' => 'Tipo de promoción',
                'estado' => 1
            ],
        ];
        DB::table('catalogo_tipos')->insert($tipos);

    }
}

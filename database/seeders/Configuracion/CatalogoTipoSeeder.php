<?php

declare(strict_types=1);

namespace Database\Seeders\Configuracion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tipos de catálogo: clasificaciones de primer nivel (catálogo_tipos).
 * Cada entrada de catálogo ("catalogos") referencia uno de estos tipos.
 */
class CatalogoTipoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            // ─── Talento humano ───
            ['codigo' => 'CARGO', 'nombre' => 'Cargo de colaborador', 'estado' => 1],
            ['codigo' => 'DEPARTAMENTO', 'nombre' => 'Departamento', 'estado' => 1],

            // ─── Clientes ───
            ['codigo' => 'TIPO_CLIENTE', 'nombre' => 'Tipo de cliente', 'estado' => 1],
            ['codigo' => 'SECTOR_COMERCIAL', 'nombre' => 'Sector comercial', 'estado' => 1],

            // ─── Inventario y compras ───
            ['codigo' => 'TIPO_MOVIMIENTO_INV', 'nombre' => 'Tipo de movimiento de inventario', 'estado' => 1],
            ['codigo' => 'CATEGORIA_PRODUCTO', 'nombre' => 'Categoría de producto', 'estado' => 1],
            ['codigo' => 'MARCA', 'nombre' => 'Marca de producto', 'estado' => 1],
            ['codigo' => 'UNIDAD_MEDIDA', 'nombre' => 'Unidad de medida', 'estado' => 1],
            ['codigo' => 'CONDICION_PAGO', 'nombre' => 'Condición de pago', 'estado' => 1],
            ['codigo' => 'TIPO_PROVEEDOR', 'nombre' => 'Tipo de proveedor', 'estado' => 1],

            // ─── Servicios y promociones ───
            ['codigo' => 'CATEGORIA_SERVICIO', 'nombre' => 'Categoría de servicio', 'estado' => 1],
            ['codigo' => 'TIPO_SERVICIO', 'nombre' => 'Tipo de servicio', 'estado' => 1],
            ['codigo' => 'TIPO_PROMOCION', 'nombre' => 'Tipo de promoción', 'estado' => 1],

            // ─── Habitaciones ───
            ['codigo' => 'CATEGORIA_HABITACION', 'nombre' => 'Categoría de habitación', 'estado' => 1],
            ['codigo' => 'TIPO_VISTA', 'nombre' => 'Tipo de vista de habitación', 'estado' => 1],
        ];

        DB::table('catalogo_tipos')->upsert($tipos, ['codigo']);
    }
}

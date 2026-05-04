<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos los IDs de los tipos para asegurar la relación
        $tipos = DB::table('catalogo_tipos')->pluck('id', 'codigo');

        // --- 1. CATEGORÍAS DE HABITACIÓN (Planos) ---
        $this->insertar($tipos['CATEGORIA_HAB'], [
            ['codigo' => 'CAT_ESTANDAR', 'nombre' => 'Estándar'],
            ['codigo' => 'CAT_SUPERIOR', 'nombre' => 'Superior'],
            ['codigo' => 'CAT_SUITE', 'nombre' => 'Suite'],
        ]);

        // --- 2. CAPACIDAD DE HABITACIÓN (Planos) ---
        $this->insertar($tipos['CAPACIDAD_HAB'], [
            ['codigo' => 'CAP_SIMPLE', 'nombre' => 'Sencilla (1 Persona)'],
            ['codigo' => 'CAP_DOBLE', 'nombre' => 'Doble (2 Personas)'],
            ['codigo' => 'CAP_TRIPLE', 'nombre' => 'Triple (3 Personas)'],
        ]);

        // --- 3. CARGOS (JERÁRQUICO) ---
        $idGerenteGral = $this->insertarGetId($tipos['CARGO'], [
            'codigo' => 'CAR_GERENTE_GRAL',
            'nombre' => 'Gerente General'
        ]);

        $this->insertar($tipos['CARGO'], [
            ['codigo' => 'CAR_GERENTE_OPS', 'nombre' => 'Gerente de Operaciones', 'padre_id' => $idGerenteGral],
            ['codigo' => 'CAR_GERENTE_ADM', 'nombre' => 'Gerente Administrativo', 'padre_id' => $idGerenteGral],
        ]);

        $idRecepcionistaJefe = $this->insertarGetId($tipos['CARGO'], [
            'codigo' => 'CAR_RECEP_JEFE',
            'nombre' => 'Jefe de Recepción'
        ]);

        $this->insertar($tipos['CARGO'], [
            ['codigo' => 'CAR_RECEP_SR', 'nombre' => 'Recepcionista Senior', 'padre_id' => $idRecepcionistaJefe],
            ['codigo' => 'CAR_RECEP_JR', 'nombre' => 'Recepcionista Junior', 'padre_id' => $idRecepcionistaJefe],
        ]);

        // --- 4. DEPARTAMENTOS (JERÁRQUICO) ---
        $idOperaciones = $this->insertarGetId($tipos['DEPARTAMENTO'], [
            'codigo' => 'DEP_OPERACIONES',
            'nombre' => 'Operaciones'
        ]);

        $this->insertar($tipos['DEPARTAMENTO'], [
            ['codigo' => 'DEP_AMA_LLAVES', 'nombre' => 'Ama de Llaves', 'padre_id' => $idOperaciones],
            ['codigo' => 'DEP_MANTENIMIENTO', 'nombre' => 'Mantenimiento', 'padre_id' => $idOperaciones],
            ['codigo' => 'DEP_RECEPCION', 'nombre' => 'Recepción', 'padre_id' => $idOperaciones],
        ]);

        // --- 5. CATEGORÍA DE SERVICIOS (JERÁRQUICO) ---
        $idAlimentos = $this->insertarGetId($tipos['CATEGORIA_SERVICIO'], [
            'codigo' => 'CAT_SERV_ALIMENTOS',
            'nombre' => 'Alimentos y Bebidas'
        ]);

        $this->insertar($tipos['CATEGORIA_SERVICIO'], [
            ['codigo' => 'CAT_SERV_REST', 'nombre' => 'Restaurante', 'padre_id' => $idAlimentos],
            ['codigo' => 'CAT_SERV_BAR', 'nombre' => 'Bar / Lounge', 'padre_id' => $idAlimentos],
        ]);

        $idBienestar = $this->insertarGetId($tipos['CATEGORIA_SERVICIO'], [
            'codigo' => 'CAT_SERV_BIENESTAR',
            'nombre' => 'Bienestar y Salud'
        ]);

        $this->insertar($tipos['CATEGORIA_SERVICIO'], [
            ['codigo' => 'CAT_SERV_SPA', 'nombre' => 'Spa y Masajes', 'padre_id' => $idBienestar],
            ['codigo' => 'CAT_SERV_GYM', 'nombre' => 'Gimnasio', 'padre_id' => $idBienestar],
        ]);

        // --- 6. OTROS CATÁLOGOS PLANOS ---
        $this->insertar($tipos['TIPO_CLIENTE'], [
            ['codigo' => 'CLI_REGULAR', 'nombre' => 'Regular'],
            ['codigo' => 'CLI_CORPORATIVO', 'nombre' => 'Corporativo'],
            ['codigo' => 'CLI_VIP', 'nombre' => 'VIP'],
        ]);

        $this->insertar($tipos['TIPO_TARIFA'], [
            ['codigo' => 'TAR_RAC', 'nombre' => 'Tarifa Rack (Pública)'],
            ['codigo' => 'TAR_CORP', 'nombre' => 'Tarifa Corporativa'],
            ['codigo' => 'TAR_PROMO', 'nombre' => 'Tarifa Promocional'],
        ]);

        $this->insertar($tipos['TIPO_MOVIMIENTO_INV'], [
            ['codigo' => 'MOV_ENTRADA', 'nombre' => 'Entrada / Compra'],
            ['codigo' => 'MOV_SALIDA', 'nombre' => 'Salida / Consumo'],
            ['codigo' => 'MOV_AJUSTE', 'nombre' => 'Ajuste de Inventario'],
        ]);
    }

    /**
     * Helper para insertar múltiples registros con timestamps
     */
    private function insertar(int $tipoId, array $data): void
    {
        foreach ($data as $item) {
            DB::table('catalogos')->insert(array_merge($item, [
                'catalogo_tipo_id' => $tipoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Helper para insertar uno solo y devolver el ID (para jerarquías)
     */
    private function insertarGetId(int $tipoId, array $item): int
    {
        return DB::table('catalogos')->insertGetId(array_merge($item, [
            'catalogo_tipo_id' => $tipoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}

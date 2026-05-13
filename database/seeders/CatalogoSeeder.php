<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
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
            'nombre' => 'Gerente General',
        ]);
        $this->insertar($tipos['CARGO'], [
            ['codigo' => 'CAR_GERENTE_OPS', 'nombre' => 'Gerente de Operaciones', 'padre_id' => $idGerenteGral],
            ['codigo' => 'CAR_GERENTE_ADM', 'nombre' => 'Gerente Administrativo', 'padre_id' => $idGerenteGral],
        ]);

        $idRecepcionistaJefe = $this->insertarGetId($tipos['CARGO'], [
            'codigo' => 'CAR_RECEP_JEFE',
            'nombre' => 'Jefe de Recepción',
        ]);
        $this->insertar($tipos['CARGO'], [
            ['codigo' => 'CAR_RECEP_SR', 'nombre' => 'Recepcionista Senior', 'padre_id' => $idRecepcionistaJefe],
            ['codigo' => 'CAR_RECEP_JR', 'nombre' => 'Recepcionista Junior', 'padre_id' => $idRecepcionistaJefe],
        ]);

        // --- 4. DEPARTAMENTOS (JERÁRQUICO) ---
        $idOperaciones = $this->insertarGetId($tipos['DEPARTAMENTO'], [
            'codigo' => 'DEP_OPERACIONES',
            'nombre' => 'Operaciones',
        ]);
        $this->insertar($tipos['DEPARTAMENTO'], [
            ['codigo' => 'DEP_AMA_LLAVES', 'nombre' => 'Ama de Llaves', 'padre_id' => $idOperaciones],
            ['codigo' => 'DEP_MANTENIMIENTO', 'nombre' => 'Mantenimiento', 'padre_id' => $idOperaciones],
            ['codigo' => 'DEP_RECEPCION', 'nombre' => 'Recepción', 'padre_id' => $idOperaciones],
        ]);

        // --- 5. CATEGORÍA DE SERVICIOS (JERÁRQUICO) ---
        $idAlimentos = $this->insertarGetId($tipos['CATEGORIA_SERVICIO'], [
            'codigo' => 'CAT_SERV_ALIMENTOS',
            'nombre' => 'Alimentos y Bebidas',
        ]);
        $this->insertar($tipos['CATEGORIA_SERVICIO'], [
            ['codigo' => 'CAT_SERV_REST', 'nombre' => 'Restaurante', 'padre_id' => $idAlimentos],
            ['codigo' => 'CAT_SERV_BAR', 'nombre' => 'Bar / Lounge', 'padre_id' => $idAlimentos],
        ]);

        $idBienestar = $this->insertarGetId($tipos['CATEGORIA_SERVICIO'], [
            'codigo' => 'CAT_SERV_BIENESTAR',
            'nombre' => 'Bienestar y Salud',
        ]);
        $this->insertar($tipos['CATEGORIA_SERVICIO'], [
            ['codigo' => 'CAT_SERV_SPA', 'nombre' => 'Spa y Masajes', 'padre_id' => $idBienestar],
            ['codigo' => 'CAT_SERV_GYM', 'nombre' => 'Gimnasio', 'padre_id' => $idBienestar],
        ]);

        // --- 6. OTROS CATÁLOGOS PLANOS (ya existentes) ---
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

        // --- 7. CATEGORÍAS DE PRODUCTO (JERÁRQUICO) ---
        $idAmenidades = $this->insertarGetId($tipos['CATEGORIA_PRODUCTO'], [
            'codigo' => 'CAT_PRO_AMENIDADES',
            'nombre' => 'Amenidades',
        ]);
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_AMEN_BANIO', 'nombre' => 'Baño', 'padre_id' => $idAmenidades],
            ['codigo' => 'CAT_PRO_AMEN_HABIT', 'nombre' => 'Habitación', 'padre_id' => $idAmenidades],
        ]);

        $idAlimentosProd = $this->insertarGetId($tipos['CATEGORIA_PRODUCTO'], [
            'codigo' => 'CAT_PRO_ALIMENTOS',
            'nombre' => 'Alimentos y Bebidas',
        ]);
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_ALIM_PEREC', 'nombre' => 'Perecederos', 'padre_id' => $idAlimentosProd],
            ['codigo' => 'CAT_PRO_ALIM_NOPER', 'nombre' => 'No perecederos', 'padre_id' => $idAlimentosProd],
        ]);

        $idLimpieza = $this->insertarGetId($tipos['CATEGORIA_PRODUCTO'], [
            'codigo' => 'CAT_PRO_LIMPIEZA',
            'nombre' => 'Limpieza y Suministros',
        ]);
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_LIMP_QUIM', 'nombre' => 'Químicos de limpieza', 'padre_id' => $idLimpieza],
            ['codigo' => 'CAT_PRO_LIMP_HERR', 'nombre' => 'Herramientas de limpieza', 'padre_id' => $idLimpieza],
        ]);

        $idActivos = $this->insertarGetId($tipos['CATEGORIA_PRODUCTO'], [
            'codigo' => 'CAT_PRO_ACTIVOS',
            'nombre' => 'Activos Fijos y Equipos',
        ]);
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_ACT_MOB', 'nombre' => 'Mobiliario', 'padre_id' => $idActivos],
            ['codigo' => 'CAT_PRO_ACT_ELECTRO', 'nombre' => 'Equipos Electrónicos', 'padre_id' => $idActivos],
            ['codigo' => 'CAT_PRO_ACT_MANT', 'nombre' => 'Herramientas de Mantenimiento', 'padre_id' => $idActivos],
        ]);

        $idBlancos = $this->insertarGetId($tipos['CATEGORIA_PRODUCTO'], [
            'codigo' => 'CAT_PRO_BLANCOS',
            'nombre' => 'Lencería y Blancos',
        ]);
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_BLAN_SABANAS', 'nombre' => 'Sábanas', 'padre_id' => $idBlancos],
            ['codigo' => 'CAT_PRO_BLAN_TOALLAS', 'nombre' => 'Toallas', 'padre_id' => $idBlancos],
            ['codigo' => 'CAT_PRO_BLAN_OTROS', 'nombre' => 'Otros textiles', 'padre_id' => $idBlancos],
        ]);

        // Categoría comodín para productos generales sin árbol
        $this->insertar($tipos['CATEGORIA_PRODUCTO'], [
            ['codigo' => 'CAT_PRO_GENERAL', 'nombre' => 'Productos Generales'],
        ]);

        // --- 8. MARCAS (Plano) ---
        $this->insertar($tipos['MARCA'], [
            ['codigo' => 'MARC_GEN', 'nombre' => 'Genérico'],
            ['codigo' => 'MARC_PG', 'nombre' => 'Procter & Gamble'],
            ['codigo' => 'MARC_KIMBERLY', 'nombre' => 'Kimberly-Clark'],
            ['codigo' => 'MARC_ECOLAB', 'nombre' => 'Ecolab'],
            ['codigo' => 'MARC_SAMSUNG', 'nombre' => 'Samsung'],
            ['codigo' => 'MARC_LG', 'nombre' => 'LG'],

        ]);

        // --- 9. UNIDADES DE MEDIDA (Plano) ---
        $this->insertar($tipos['UNIDAD_MEDIDA'], [
            ['codigo' => 'UNI_UD', 'nombre' => 'Unidad'],
            ['codigo' => 'UNI_KG', 'nombre' => 'Kilogramo'],
            ['codigo' => 'UNI_GR', 'nombre' => 'Gramo'],
            ['codigo' => 'UNI_LIT', 'nombre' => 'Litro'],
            ['codigo' => 'UNI_ML', 'nombre' => 'Mililitro'],
            ['codigo' => 'UNI_CAJA', 'nombre' => 'Caja'],
            ['codigo' => 'UNI_PAQ', 'nombre' => 'Paquete'],
            ['codigo' => 'UNI_METRO', 'nombre' => 'Metro'],
        ]);
    }

    // -------------------- HELPERS --------------------
    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    private function insertar(int $tipoId, array $data): void
    {
        foreach ($data as $item) {
            /** @var array<string, mixed> $item */
            DB::table('catalogos')->insert(array_merge($item, [
                'catalogo_tipo_id' => $tipoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * @param  array<string, mixed>  $item
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

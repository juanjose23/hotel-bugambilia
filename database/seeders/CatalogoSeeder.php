<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = DB::table('catalogo_tipos')->pluck('id', 'codigo');

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

        // --- 5. CATEGORÍA DE SERVICIOS (PLANOS / JERÁRQUICOS) ---
        $this->insertar($tipos['CATEGORIA_SERVICIO'], [
            ['codigo' => 'CAT_SERV_ALOJAMIENTO', 'nombre' => 'Alojamiento y Estancia'],
            ['codigo' => 'CAT_SERV_BIENESTAR', 'nombre' => 'Bienestar y Relajación'],
            ['codigo' => 'CAT_SERV_TRANSPORTE', 'nombre' => 'Transporte y Logística'],
            ['codigo' => 'CAT_SERV_LAVANDERIA', 'nombre' => 'Lavandería y Limpieza'],
            ['codigo' => 'CAT_SERV_NEGOCIOS', 'nombre' => 'Negocios y Eventos'],
            ['codigo' => 'CAT_SERV_RECREACION', 'nombre' => 'Recreación y Entretenimiento'],
            ['codigo' => 'CAT_SERV_VIP', 'nombre' => 'Servicios VIP y Personalizados'],
            ['codigo' => 'CAT_SERV_TECNOLOGIA', 'nombre' => 'Tecnología y Conectividad'],
        ]);

        // --- 6. OTROS CATÁLOGOS PLANOS (ya existentes) ---
        $this->insertar($tipos['TIPO_CLIENTE'], [
            ['codigo' => 'CLI_REGULAR', 'nombre' => 'Regular'],
            ['codigo' => 'CLI_CORPORATIVO', 'nombre' => 'Corporativo'],
            ['codigo' => 'CLI_VIP', 'nombre' => 'VIP'],
        ]);

        $this->insertar($tipos['TIPO_MOVIMIENTO_INV'], [
            ['codigo' => 'MOV_ENTRADA', 'nombre' => 'Entrada / Compra'],
            ['codigo' => 'MOV_SALIDA', 'nombre' => 'Salida / Consumo'],
            ['codigo' => 'MOV_AJUSTE', 'nombre' => 'Ajuste de Inventario'],
            ['codigo' => 'MOV_TRANSFERENCIA', 'nombre' => 'Transferencia entre almacenes'],
            ['codigo' => 'ENTRADA_RECEPCION', 'nombre' => 'Recepción de compra'],
            ['codigo' => 'TRASLADO', 'nombre' => 'Distribución interna'],
            ['codigo' => 'CONSUMO', 'nombre' => 'Consumo / Merma / Ajuste'],
            ['codigo' => 'AJUSTE', 'nombre' => 'Ajuste físico'],
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
            ['codigo' => 'CAT_PRO_inv_MOB', 'nombre' => 'Mobiliario', 'padre_id' => $idActivos],
            ['codigo' => 'CAT_PRO_inv_ELECTRO', 'nombre' => 'Equipos Electrónicos', 'padre_id' => $idActivos],
            ['codigo' => 'CAT_PRO_inv_MANT', 'nombre' => 'Herramientas de Mantenimiento', 'padre_id' => $idActivos],
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

        // --- 10. CONDICIONES DE PAGO (Plano) ---
        $this->insertar($tipos['CONDICION_PAGO'], [
            ['codigo' => 'PAG_CONTADO', 'nombre' => 'Contado'],
            ['codigo' => 'PAG_15D', 'nombre' => '15 días'],
            ['codigo' => 'PAG_30D', 'nombre' => '30 días'],
            ['codigo' => 'PAG_45D', 'nombre' => '45 días'],
            ['codigo' => 'PAG_60D', 'nombre' => '60 días'],
            ['codigo' => 'PAG_90D', 'nombre' => '90 días'],
        ]);

        // --- 11. CATEGORÍAS DE HABITACIONES (Plano) ---
        $this->insertar($tipos['CATEGORIA_HABITACION'], [
            ['codigo' => 'CAT_HAB_ESTANDAR', 'nombre' => 'Estándar'],
            ['codigo' => 'CAT_HAB_DELUXE', 'nombre' => 'Deluxe'],
            ['codigo' => 'CAT_HAB_SUITE', 'nombre' => 'Suite'],
            ['codigo' => 'CAT_HAB_PRESIDENCIAL', 'nombre' => 'Presidencial'],
            ['codigo' => 'CAT_HAB_FAMILIAR', 'nombre' => 'Familiar'],
        ]);

        // --- 13. VISTAS DE HABITACIONES (Plano) ---
        $this->insertar($tipos['TIPO_VISTA'], [
            ['codigo' => 'VISTA_MAR', 'nombre' => 'Vista al Mar'],
            ['codigo' => 'VISTA_CIUDAD', 'nombre' => 'Vista a la Ciudad'],
            ['codigo' => 'VISTA_JARDIN', 'nombre' => 'Vista al Jardín'],
            ['codigo' => 'VISTA_MONTANA', 'nombre' => 'Vista a la Montaña'],
            ['codigo' => 'VISTA_PISCINA', 'nombre' => 'Vista a la Piscina'],
            ['codigo' => 'VISTA_INTERIOR', 'nombre' => 'Vista Interior'],
        ]);

        // --- 14. TIPOS DE PROVEEDOR (Plano) ---
        $this->insertar($tipos['TIPO_PROVEEDOR'], [
            ['codigo' => 'PROV_NACIONAL', 'nombre' => 'Nacional'],
            ['codigo' => 'PROV_INTERNACIONAL', 'nombre' => 'Internacional'],
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
            DB::table('catalogos')->upsert(
                array_merge($item, [
                    'catalogo_tipo_id' => $tipoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
                ['catalogo_tipo_id', 'codigo']
            );
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function insertarGetId(int $tipoId, array $item): int
    {
        $data = array_merge($item, [
            'catalogo_tipo_id' => $tipoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('catalogos')->updateOrInsert(
            ['catalogo_tipo_id' => $tipoId, 'codigo' => $item['codigo']],
            $data
        );

        return DB::table('catalogos')
            ->where('catalogo_tipo_id', $tipoId)
            ->where('codigo', $item['codigo'])
            ->value('id');
    }
}

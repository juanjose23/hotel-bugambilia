<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Módulo de Compras: Triggers y Funciones de Integridad Operativa
     *
     * Implementa lógica de negocio a nivel de base de datos para:
     * - Validar que las cantidades recibidas no excedan lo ordenado.
     * - Garantizar la integridad transaccional en el proceso de recepción.
     */
    public function up(): void
    {
        // Función para validar cantidades en recepción
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION chk_cantidad_recepcion()
            RETURNS TRIGGER AS $$
            DECLARE
                cantidad_ordenada DECIMAL;
                total_recibido DECIMAL;
            BEGIN
                -- Obtener la cantidad total pactada en la Orden de Compra
                SELECT cantidad INTO cantidad_ordenada 
                FROM orden_compra_items 
                WHERE id = NEW.orden_item_id;

                -- Calcular lo ya recibido previamente (excluyendo el registro actual si es un update)
                SELECT COALESCE(SUM(cantidad_recibida), 0) INTO total_recibido 
                FROM recepcion_items 
                WHERE orden_item_id = NEW.orden_item_id 
                AND id != COALESCE(NEW.id, 0);

                -- Validar exceso
                IF (total_recibido + NEW.cantidad_recibida) > cantidad_ordenada THEN
                    RAISE EXCEPTION 'CONTROL INDUSTRIAL: La cantidad recibida (%) supera la cantidad ordenada (%) para el item ID: %', 
                        (total_recibido + NEW.cantidad_recibida), 
                        cantidad_ordenada, 
                        NEW.orden_item_id;
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);

        // Trigger vinculado a recepcion_items
        DB::statement('DROP TRIGGER IF EXISTS trg_chk_cantidad_recepcion ON recepcion_items');
        DB::statement('CREATE TRIGGER trg_chk_cantidad_recepcion BEFORE INSERT OR UPDATE ON recepcion_items FOR EACH ROW EXECUTE FUNCTION chk_cantidad_recepcion();');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_chk_cantidad_recepcion ON recepcion_items');
        DB::statement('DROP FUNCTION IF EXISTS chk_cantidad_recepcion()');
    }
};

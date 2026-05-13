<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega el valor 'almacen' a la constraint CHECK de la columna tipo
     * en la tabla ubicaciones, para soportar el nuevo tipo de ubicación
     * que permite usar la jerarquía como almacén de inventario.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE ubicaciones DROP CONSTRAINT ubicaciones_tipo_check');
        DB::statement("ALTER TABLE ubicaciones ADD CONSTRAINT ubicaciones_tipo_check CHECK (tipo::text = ANY (ARRAY['edificio', 'piso', 'sector', 'zona', 'almacen']::text[]))");
    }

    public function down(): void
    {
        DB::statement("UPDATE ubicaciones SET tipo = 'zona' WHERE tipo = 'almacen'");
        DB::statement('ALTER TABLE ubicaciones DROP CONSTRAINT ubicaciones_tipo_check');
        DB::statement("ALTER TABLE ubicaciones ADD CONSTRAINT ubicaciones_tipo_check CHECK (tipo::text = ANY (ARRAY['edificio', 'piso', 'sector', 'zona']::text[]))");
    }
};

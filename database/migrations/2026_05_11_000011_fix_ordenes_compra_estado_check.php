<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrección de constraint de estados en órdenes de compra.
     *
     * Extiende el CHECK de estados válidos de (1-5) a (1-8)
     * para incluir los nuevos estados del Enum EstadoOrdenCompra.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE ordenes_compra DROP CONSTRAINT IF EXISTS chk_ordenes_estado');
        DB::statement('ALTER TABLE ordenes_compra ADD CONSTRAINT chk_ordenes_estado CHECK (estado IN (1,2,3,4,5,6,7,8))');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE ordenes_compra DROP CONSTRAINT IF EXISTS chk_ordenes_estado');
        DB::statement('ALTER TABLE ordenes_compra ADD CONSTRAINT chk_ordenes_estado CHECK (estado IN (1,2,3,4,5))');
    }
};

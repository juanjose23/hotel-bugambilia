<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Altera la restricción check del tipo de producto para incluir activos fijos (tipo=3).
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE productos DROP CONSTRAINT IF EXISTS chk_tipo_productos');
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_tipo_productos CHECK (tipo IN (1, 2, 3))');
        }
    }

    /**
     * Revierte el check a (1, 2).
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE productos DROP CONSTRAINT IF EXISTS chk_tipo_productos');
            DB::statement('ALTER TABLE productos ADD CONSTRAINT chk_tipo_productos CHECK (tipo IN (1, 2))');
        }
    }
};

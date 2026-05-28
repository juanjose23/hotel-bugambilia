<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE habitaciones DROP CONSTRAINT chk_habitaciones_estado');
            DB::statement('ALTER TABLE habitaciones ADD CONSTRAINT chk_habitaciones_estado CHECK (estado IN (0, 1, 2, 3, 4, 5, 6))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE habitaciones DROP CONSTRAINT chk_habitaciones_estado');
            DB::statement('ALTER TABLE habitaciones ADD CONSTRAINT chk_habitaciones_estado CHECK (estado IN (0, 1, 2, 3, 4, 5))');
        }
    }
};

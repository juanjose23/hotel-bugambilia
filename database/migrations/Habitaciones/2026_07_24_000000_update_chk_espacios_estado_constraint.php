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
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE espacios DROP CONSTRAINT IF EXISTS chk_espacios_estado');
            DB::statement('ALTER TABLE espacios ADD CONSTRAINT chk_espacios_estado CHECK (estado IN (0, 1, 2, 3, 4, 5, 6))');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE espacios DROP CONSTRAINT IF EXISTS chk_espacios_estado');
            DB::statement('ALTER TABLE espacios ADD CONSTRAINT chk_espacios_estado CHECK (estado IN (0, 1, 2, 3, 4, 5))');
        }
    }
};

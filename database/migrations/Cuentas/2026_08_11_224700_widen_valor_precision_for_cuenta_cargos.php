<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cuenta_cargos ALTER COLUMN valor TYPE NUMERIC(12, 4)');
            DB::statement('ALTER TABLE cargos_facturacion ALTER COLUMN valor TYPE NUMERIC(12, 4)');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cuenta_cargos ALTER COLUMN valor TYPE NUMERIC(8, 4)');
            DB::statement('ALTER TABLE cargos_facturacion ALTER COLUMN valor TYPE NUMERIC(8, 4)');
        }
    }
};

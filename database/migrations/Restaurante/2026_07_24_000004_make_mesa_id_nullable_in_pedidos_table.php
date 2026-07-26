<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedidos') && Schema::hasColumn('pedidos', 'mesa_id')) {
            DB::statement('ALTER TABLE pedidos ALTER COLUMN mesa_id DROP NOT NULL;');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedidos') && Schema::hasColumn('pedidos', 'mesa_id')) {
            DB::statement('ALTER TABLE pedidos ALTER COLUMN mesa_id SET NOT NULL;');
        }
    }
};

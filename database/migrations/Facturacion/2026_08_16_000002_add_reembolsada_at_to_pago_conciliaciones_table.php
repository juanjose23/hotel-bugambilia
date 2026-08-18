<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago_conciliaciones', function (Blueprint $table): void {
            $table->timestamp('reembolsada_at')->nullable()->after('conciliada_at');
            $table->dropColumn('meta_datos');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pago_conciliaciones DROP CONSTRAINT IF EXISTS chk_pc_estado');
            DB::statement('ALTER TABLE pago_conciliaciones ADD CONSTRAINT chk_pc_estado CHECK (estado IN (1, 2, 3, 4, 5))');
        }
    }

    public function down(): void
    {
        Schema::table('pago_conciliaciones', function (Blueprint $table): void {
            $table->jsonb('meta_datos')->nullable()->after('observaciones');
            $table->dropColumn('reembolsada_at');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pago_conciliaciones DROP CONSTRAINT IF EXISTS chk_pc_estado');
            DB::statement('ALTER TABLE pago_conciliaciones ADD CONSTRAINT chk_pc_estado CHECK (estado IN (1, 2, 3, 4))');
        }
    }
};

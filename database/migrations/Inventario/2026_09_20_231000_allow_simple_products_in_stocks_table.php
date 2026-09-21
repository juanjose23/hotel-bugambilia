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
        Schema::table('stocks', function (Blueprint $table): void {
            $table->foreignId('producto_id')
                ->nullable()
                ->after('stockable_id')
                ->comment('FK al producto de catálogo base. Permite productos sin variante.')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('producto_variante_id')
                ->nullable()
                ->change();

            $table->index('producto_id');
        });

        // Rellenar producto_id desde producto_variantes para los registros existentes
        DB::statement('UPDATE stocks SET producto_id = (SELECT producto_id FROM producto_variantes WHERE producto_variantes.id = stocks.producto_variante_id) WHERE producto_variante_id IS NOT NULL AND producto_id IS NULL');

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE stocks DROP CONSTRAINT IF EXISTS uq_stock_variante');
            DB::statement('DROP INDEX IF EXISTS uq_stock_variante');
            DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, producto_id, COALESCE(producto_variante_id, 0)) WHERE deleted_at IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS uq_stock_variante');
            DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, producto_id, COALESCE(producto_variante_id, 0)) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table): void {
            $table->dropForeign(['producto_id']);
            $table->dropIndex(['producto_id']);
            $table->dropColumn(['producto_id']);
        });
    }
};

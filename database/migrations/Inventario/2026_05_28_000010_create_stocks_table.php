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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->morphs('stockable');
            $table->foreignId('producto_variante_id')->constrained('producto_variantes');
            $table->foreignId('lote_id')->nullable()->constrained('inv_lotes')->nullOnDelete();
            $table->decimal('cantidad_ideal', 12, 4);
            $table->decimal('cantidad_actual', 12, 4);
            $table->string('estado', 20)->nullable();
            $table->timestamp('ultima_verificacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['stockable_type', 'stockable_id', 'producto_variante_id', 'deleted_at'], 'uq_stock_variante');
            $table->index('lote_id');
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE stocks DROP CONSTRAINT IF EXISTS uq_stock_variante');
            DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, producto_variante_id) WHERE deleted_at IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS uq_stock_variante');
            DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, producto_variante_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('stocks', fn (Blueprint $t) => $t->dropIndex(['lote_id']));
        Schema::dropIfExists('stocks');
    }
};

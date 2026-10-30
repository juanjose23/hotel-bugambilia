<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS uq_stock_variante');
        DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, (CASE WHEN producto_variante_id IS NULL THEN COALESCE(producto_id, 0) ELSE 0 END), COALESCE(producto_variante_id, 0), COALESCE(lote_id, 0)) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        $duplicados = DB::table('stocks')->whereNull('deleted_at')->select('stockable_type', 'stockable_id', 'producto_id', 'producto_variante_id')->groupBy('stockable_type', 'stockable_id', 'producto_id', 'producto_variante_id')->havingRaw('COUNT(*) > 1')->exists();
        if ($duplicados) {
            throw new RuntimeException('No se puede revertir: existen lotes separados que el índice anterior no admite.');
        }
        DB::statement('DROP INDEX IF EXISTS uq_stock_variante');
        DB::statement('CREATE UNIQUE INDEX uq_stock_variante ON stocks (stockable_type, stockable_id, producto_id, COALESCE(producto_variante_id, 0)) WHERE deleted_at IS NULL');
    }
};

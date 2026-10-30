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
        Schema::table('procesos_cocina', function (Blueprint $table): void {
            $table->string('tipo_registro', 25)->default('historico');
            $table->timestamp('confirmado_at')->nullable();
            $table->foreignId('lote_resultado_id')->nullable()->constrained('inv_lotes')->restrictOnDelete();
            $table->decimal('cantidad_obtenida', 14, 4)->nullable();
            $table->foreignId('pedido_item_id')->nullable()->unique()->constrained('pedido_items')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('procesos_cocina')->whereNotNull('confirmado_at')->exists()) {
            throw new RuntimeException('No se puede revertir mientras existan producciones confirmadas.');
        }
        Schema::table('procesos_cocina', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('pedido_item_id');
            $table->dropConstrainedForeignId('lote_resultado_id');
            $table->dropColumn(['confirmado_at', 'cantidad_obtenida', 'tipo_registro']);
        });
    }
};

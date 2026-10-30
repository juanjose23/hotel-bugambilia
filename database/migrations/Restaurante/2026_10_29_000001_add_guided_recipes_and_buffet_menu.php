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
        Schema::table('platos', function (Blueprint $table): void {
            $table->text('instrucciones_preparacion')->nullable();
        });
        Schema::table('restaurante_buffets', function (Blueprint $table): void {
            $table->string('tipo_servicio', 20)->default('sin_definir');
        });
        Schema::create('restaurante_buffet_platos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('buffet_id')->constrained('restaurante_buffets')->restrictOnDelete();
            $table->foreignId('plato_id')->constrained('platos')->restrictOnDelete();
            $table->foreignId('producto_variante_id')->constrained('producto_variantes')->restrictOnDelete();
            $table->unsignedInteger('porciones_previstas');
            $table->timestamps();
            $table->unique(['buffet_id', 'plato_id']);
            $table->unique(['buffet_id', 'producto_variante_id']);
        });
    }

    public function down(): void
    {
        if (DB::table('restaurante_buffet_platos')->exists() || DB::table('platos')->whereNotNull('instrucciones_preparacion')->exists() || DB::table('restaurante_buffets')->where('tipo_servicio', '!=', 'sin_definir')->exists()) {
            throw new RuntimeException('Hay menús o instrucciones registrados. Exporte y preserve esos datos antes de revertir.');
        }
        Schema::dropIfExists('restaurante_buffet_platos');
        Schema::table('restaurante_buffets', fn (Blueprint $table) => $table->dropColumn('tipo_servicio'));
        Schema::table('platos', fn (Blueprint $table) => $table->dropColumn('instrucciones_preparacion'));
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procesos_cocina', function (Blueprint $table) {
            $table->foreignId('plato_id')->nullable()->after('codigo')->constrained('platos')->nullOnDelete();
            $table->unsignedSmallInteger('cantidad_platos')->nullable()->after('plato_id')->comment('Número de platos a producir desde la receta');
        });
    }

    public function down(): void
    {
        Schema::table('procesos_cocina', function (Blueprint $table) {
            $table->dropForeign(['plato_id']);
            $table->dropColumn(['plato_id', 'cantidad_platos']);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('limp_horario_turnos', function (Blueprint $table) {
            $table->boolean('es_lavanderia')->default(false)->after('nombre');
            $table->index('es_lavanderia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('limp_horario_turnos', function (Blueprint $table) {
            $table->dropIndex(['es_lavanderia']);
            $table->dropColumn('es_lavanderia');
        });
    }
};

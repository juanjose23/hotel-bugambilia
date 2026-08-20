<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('limp_horarios', function (Blueprint $table): void {
            $table->unsignedSmallInteger('duracion_estimada_minutos')
                ->default(30)
                ->after('hora_estimada')
                ->comment('Minutos estimados para limpiar cada destino del horario.');
        });
    }

    public function down(): void
    {
        Schema::table('limp_horarios', function (Blueprint $table): void {
            $table->dropColumn('duracion_estimada_minutos');
        });
    }
};
